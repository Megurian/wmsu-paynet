<?php

namespace App\Http\Controllers;

use App\Models\PromissoryNote;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\User;
use App\Notifications\PromissoryNoteSignaturePendingNotification;
use App\Services\PromissoryNoteIssuanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdviserPromissoryNoteController extends Controller
{
    public function index(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $adviserId = Auth::id();
        $tab = $request->get('tab', 'pending_adviser_verification');

        $statusMap = [
            'pending_adviser_verification' => PromissoryNote::STATUS_PENDING_ADVISER_VERIFICATION,
            'pending_verification' => PromissoryNote::STATUS_PENDING_VERIFICATION,
            'active' => PromissoryNote::STATUS_ACTIVE,
            'closed' => PromissoryNote::STATUS_CLOSED,
            'default' => PromissoryNote::STATUS_DEFAULT,
            'bad_debt' => PromissoryNote::STATUS_BAD_DEBT,
            'voided' => PromissoryNote::STATUS_VOIDED,
            'all' => 'all',
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
        ])->whereHas('enrollment', function ($query) use ($collegeId, $adviserId, $selectedSchoolYear, $selectedSemester) {
            $query->where('college_id', $collegeId)
                ->where('adviser_id', $adviserId);

            if ($selectedSchoolYear) {
                $query->where('school_year_id', $selectedSchoolYear->id);
            }

            if ($selectedSemester) {
                $query->where('semester_id', $selectedSemester->id);
            }
        });

        $counts = [];
        foreach ($statusMap as $tabKey => $status) {
            if ($status === 'all') {
                $counts[$tabKey] = (clone $baseQuery)->count();
            } else {
                $counts[$tabKey] = (clone $baseQuery)->where('status', $status)->count();
            }
        }

        $query = clone $baseQuery;
        if (isset($statusMap[$tab]) && $statusMap[$tab] !== 'all') {
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
        ))->with([
            'reviewerTitle' => 'Adviser Promissory Note Review',
            'reviewerNotesLabel' => 'Adviser Notes',
            'approvalRoutePrefix' => 'adviser.promissory_notes',
            'dashboardRouteName' => null,
            'pendingStatus' => PromissoryNote::STATUS_PENDING_ADVISER_VERIFICATION,
            'pendingHeadingText' => 'Awaiting adviser review',
            'pendingPanelText' => 'Your adviser is reviewing the uploaded note before it is passed to the coordinator.',
            'approvalButtonText' => 'Approve for Coordinator Review',
            'rejectButtonText' => 'Reject and Send Back for Re-signing',
            'tabs' => [
                'pending_adviser_verification' => 'Pending Adviser Review',
                'pending_verification' => 'Pending Coordinator Review',
                'active' => 'Active',
                'closed' => 'Closed',
                'default' => 'Default',
                'bad_debt' => 'Bad Debt',
                'voided' => 'Voided',
                'all' => 'All',
            ],
        ]);
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

            if (! $lockedNote->isPendingAdviserVerification()) {
                abort(409, 'Only notes awaiting adviser review may be approved.');
            }

            $lockedNote->status = PromissoryNote::STATUS_PENDING_VERIFICATION;
            $lockedNote->adviser_reviewed_by = Auth::id();
            $lockedNote->adviser_reviewed_at = now();

            if (! empty($validated['review_notes'])) {
                $lockedNote->adviser_review_notes = trim($validated['review_notes']);
                $lockedNote->notes = trim((($lockedNote->notes ?? '') !== '' ? $lockedNote->notes . "\n\n" : '') . 'Adviser approval: ' . $validated['review_notes']);
            }

            $lockedNote->save();
        });

        $note->refresh()->load(['student', 'enrollment']);

        Log::info('Promissory note adviser approved', [
            'promissory_note_id' => $note->id,
            'student_id' => $note->student_id,
            'approved_by' => Auth::id(),
            'review_notes_provided' => ! empty($validated['review_notes'] ?? null),
        ]);

        $this->notifyCoordinatorReviewers($note);

        return back()->with('status', 'Promissory note approved and forwarded to coordinator review.');
    }

    public function rejectSignature(Request $request, PromissoryNote $note)
    {
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->authorizeNote($note);

        DB::transaction(function () use ($note, $validated) {
            $lockedNote = PromissoryNote::whereKey($note->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedNote->isPendingAdviserVerification()) {
                abort(409, 'Only notes awaiting adviser review may be rejected.');
            }

            if ($lockedNote->document_path && Storage::disk('local')->exists($lockedNote->document_path)) {
                Storage::disk('local')->delete($lockedNote->document_path);
            }

            $lockedNote->forceFill([
                'status' => PromissoryNote::STATUS_PENDING_SIGNATURE,
                'signed_at' => null,
                'signed_by' => null,
                'document_path' => null,
            ]);

            if (! empty($validated['review_notes'])) {
                $lockedNote->notes = trim((($lockedNote->notes ?? '') !== '' ? $lockedNote->notes . "\n\n" : '') . 'Adviser rejection: ' . $validated['review_notes']);
            }

            $lockedNote->save();
        });

        Log::info('Promissory note adviser rejected', [
            'promissory_note_id' => $note->id,
            'student_id' => $note->student_id,
            'rejected_by' => Auth::id(),
            'review_notes_provided' => ! empty($validated['review_notes'] ?? null),
        ]);

        return back()->with('status', 'Promissory note returned to student for re-signing.');
    }

    private function authorizeNote(PromissoryNote $note): void
    {
        $user = Auth::user();
        abort_unless($user, 403);
        abort_unless($user->hasRole('adviser'), 403);
        abort_unless($note->enrollment, 404);
        abort_unless((int) $note->enrollment->college_id === (int) $user->college_id, 403);
        abort_unless((int) $note->enrollment->adviser_id === (int) $user->id, 403);
    }

    private function notifyCoordinatorReviewers(PromissoryNote $note): void
    {
        $note->loadMissing(['student', 'enrollment', 'fees']);

        $reviewers = User::where('role', 'student_coordinator')
            ->where('college_id', $note->enrollment->college_id)
            ->cursor();

        $sent = false;
        foreach ($reviewers as $reviewer) {
            $sent = true;
            $reviewer->notify(new PromissoryNoteSignaturePendingNotification(
                $note->fresh(['student', 'enrollment', 'fees']),
                route('college.promissory_notes.index', ['tab' => 'pending_verification']),
                'Promissory note awaiting coordinator review',
                'A promissory note has been approved by the adviser and is waiting for your review.'
            ));
        }

        if (! $sent && $note->issuedBy) {
            $note->issuedBy->notify(new PromissoryNoteSignaturePendingNotification(
                $note->fresh(['student', 'enrollment', 'fees']),
                route('college.promissory_notes.index', ['tab' => 'pending_verification']),
                'Promissory note awaiting coordinator review',
                'A promissory note has been approved by the adviser and is waiting for your review.'
            ));
        }
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
