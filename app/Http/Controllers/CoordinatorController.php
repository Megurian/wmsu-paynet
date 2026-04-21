<?php

namespace App\Http\Controllers;

use App\Exports\PromissoryNoteReportExport;
use App\Models\PromissoryNote;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\User;
use App\Notifications\PromissoryNoteSignatureApprovedNotification;
use App\Notifications\PromissoryNoteSignaturePendingNotification;
use App\Services\PromissoryNoteIssuanceService;
use App\Services\PromissoryNoteReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CoordinatorController extends Controller
{
    public function index(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $tab = $request->get('tab', 'pending_verification');

        $statusMap = [
            'pending_verification' => PromissoryNote::STATUS_PENDING_VERIFICATION,
            'pending_signature' => PromissoryNote::STATUS_PENDING_SIGNATURE,
            'active' => PromissoryNote::STATUS_ACTIVE,
            'closed' => PromissoryNote::STATUS_CLOSED,
            'default' => PromissoryNote::STATUS_DEFAULT,
            'bad_debt' => PromissoryNote::STATUS_BAD_DEBT,
            'voided' => PromissoryNote::STATUS_VOIDED,
        ];

        $context = $this->resolveReportingContext($request, false);
        $schoolYears = $context['schoolYears'];
        $semesters = $context['semesters'];
        $selectedSchoolYear = $context['selectedSchoolYear'];
        $selectedSemester = $context['selectedSemester'];

        $search = trim((string) $request->get('search', ''));
        $documentFilter = $request->get('document_filter', 'all');

        $baseQuery = PromissoryNote::with([
            'student',
            'enrollment.course',
            'enrollment.yearLevel',
            'enrollment.section',
            'enrollment.schoolYear',
            'enrollment.semester',
            'issuedBy',
            'fees.organization',
        ])->whereHas('enrollment', function ($query) use ($collegeId, $selectedSchoolYear, $selectedSemester) {
            $query->where('college_id', $collegeId);

            if ($selectedSchoolYear) {
                $query->where('school_year_id', $selectedSchoolYear->id);
            }

            if ($selectedSemester) {
                $query->where('semester_id', $selectedSemester->id);
            }
        });

        $counts = [];
        foreach ($statusMap as $tabKey => $status) {
            $counts[$tabKey] = (clone $baseQuery)->where('status', $status)->count();
        }

        $query = clone $baseQuery;
        if (isset($statusMap[$tab])) {
            $query->where('status', $statusMap[$tab]);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($query) use ($search) {
                        $query->where('student_id', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($documentFilter === 'has_document') {
            $query->whereNotNull('document_path');
        } elseif ($documentFilter === 'no_document') {
            $query->whereNull('document_path');
        }

        $notes = $query
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('college.promissory_notes_approval', compact(
            'notes',
            'tab',
            'counts',
            'search',
            'documentFilter',
            'schoolYears',
            'semesters',
            'selectedSchoolYear',
            'selectedSemester'
        ));
    }

    public function dashboard(Request $request, PromissoryNoteReportingService $reportingService)
    {
        abort_unless(Auth::user(), 403);

        $context = $this->resolveReportingContext($request);

        $report = $reportingService->buildCollegeDashboard(
            Auth::user()->college_id,
            $context['selectedSchoolYear']?->id,
            $context['selectedSemester']?->id
        );

        return view('college.promissory_notes_dashboard', array_merge($report, $context));
    }

    public function export(Request $request, PromissoryNoteReportingService $reportingService)
    {
        abort_unless(Auth::user(), 403);

        $context = $this->resolveReportingContext($request);

        $query = $reportingService->exportCollegeQuery(
            Auth::user()->college_id,
            $context['selectedSchoolYear']?->id,
            $context['selectedSemester']?->id
        );

        $schoolYearSlug = $context['selectedSchoolYear'] ? sprintf('%s-%s', $context['selectedSchoolYear']->sy_start->format('Y'), $context['selectedSchoolYear']->sy_end->format('Y')) : 'all-years';
        $semesterSlug = $context['selectedSemester'] ? str_replace(' ', '-', strtolower($context['selectedSemester']->name)) : 'all-semesters';
        $filename = sprintf('promissory-note-report-%s-%s-%s.csv', $schoolYearSlug, $semesterSlug, now()->format('Ymd'));

        Log::info('Promissory note report exported', [
            'requested_by' => Auth::id(),
            'college_id' => Auth::user()->college_id,
            'school_year_id' => $context['selectedSchoolYear']?->id,
            'semester_id' => $context['selectedSemester']?->id,
            'filename' => $filename,
        ]);

        return Excel::download(new PromissoryNoteReportExport($query), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function viewDocument(PromissoryNote $note)
    {
        $this->authorizeNote($note);

        if (! $note->document_path || ! Storage::disk('local')->exists($note->document_path)) {
            abort(404, 'Uploaded document not found.');
        }

        return response()->file(
            Storage::disk('local')->path($note->document_path),
            [
                'Content-Disposition' => 'inline; filename="' . basename($note->document_path) . '"',
            ]
        );
    }

    public function approveSignature(Request $request, PromissoryNote $note)
    {
        $validated = $request->validate([
            'review_confirmed' => ['accepted'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->authorizeNote($note);

        DB::transaction(function () use ($note, $validated) {
            $lockedNote = PromissoryNote::whereKey($note->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedNote->isPendingVerification()) {
                abort(409, 'Only notes awaiting verification may be approved.');
            }

            $lockedNote->status = PromissoryNote::STATUS_ACTIVE;

            if (! empty($validated['review_notes'])) {
                $lockedNote->notes = trim((($lockedNote->notes ?? '') !== '' ? $lockedNote->notes . "\n\n" : '') . 'Coordinator approval: ' . $validated['review_notes']);
            }

            $lockedNote->save();
        });

        $note->refresh()->load(['student', 'enrollment']);

        Log::info('Promissory note signature approved', [
            'promissory_note_id' => $note->id,
            'student_id' => $note->student_id,
            'approved_by' => Auth::id(),
            'review_notes_provided' => ! empty($validated['review_notes'] ?? null),
        ]);

        if ($note->student?->email) {
            $note->student->notify(new PromissoryNoteSignatureApprovedNotification($note));
        }

        return back()->with('status', 'Promissory note approved and activated.');
    }

    public function rejectSignature(Request $request, PromissoryNote $note, PromissoryNoteIssuanceService $issuanceService)
    {
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->authorizeNote($note);

        $issuanceService->rejectSignature($note, $validated['review_notes'] ?? null);

        Log::info('Promissory note signature rejected', [
            'promissory_note_id' => $note->id,
            'student_id' => $note->student_id,
            'rejected_by' => Auth::id(),
            'review_notes_provided' => ! empty($validated['review_notes'] ?? null),
        ]);

        return back()->with('status', 'Promissory note returned for re-signing.');
    }

    private function authorizeNote(PromissoryNote $note): void
    {
        $user = Auth::user();
        abort_unless($user, 403);
        abort_unless($user->isStudentCoordinator(), 403);
        abort_unless($note->enrollment, 404);
        abort_unless((int) $note->enrollment->college_id === (int) $user->college_id, 403);
    }

    private function resolveReportingContext(Request $request, bool $defaultToActive = true): array
    {
        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();

        $selectedSchoolYear = null;
        if ($request->filled('school_year_id')) {
            $selectedSchoolYear = SchoolYear::findOrFail((int) $request->input('school_year_id'));
        } elseif ($defaultToActive) {
            $selectedSchoolYear = SchoolYear::where('is_active', true)->first() ?? SchoolYear::orderByDesc('sy_start')->first();
        }

        $semesters = Semester::when($selectedSchoolYear, function ($query) use ($selectedSchoolYear) {
            $query->where('school_year_id', $selectedSchoolYear->id);
        })->orderBy('school_year_id', 'desc')->orderBy('id')->get();

        $selectedSemester = null;
        if ($request->filled('semester_id')) {
            $selectedSemester = Semester::findOrFail((int) $request->input('semester_id'));
        } elseif ($defaultToActive && $selectedSchoolYear) {
            $selectedSemester = $semesters->firstWhere('is_active', true) ?? $semesters->first();
        }

        return [
            'schoolYears' => $schoolYears,
            'semesters' => $semesters,
            'selectedSchoolYear' => $selectedSchoolYear,
            'selectedSemester' => $selectedSemester,
        ];
    }
}