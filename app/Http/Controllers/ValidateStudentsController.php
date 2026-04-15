<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\PromissoryNote;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
use App\Services\PromissoryNoteIssuanceService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateExport;
use App\Imports\StudentsImport;
use App\Imports\StudentsImportPreview;

class ValidateStudentsController extends Controller
{


    public function index(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $studentsQuery = Student::whereHas('enrollments', function ($e) use ($collegeId, $request) {
            $e->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('student_enrollments')
                    ->groupBy('student_id');
            })
                ->where('college_id', $collegeId);

            if ($request->course) {
                $e->where('course_id', $request->course);
            }
            if ($request->year) {
                $e->where('year_level_id', $request->year);
            }
            if ($request->section) {
                $e->where('section_id', $request->section);
            }
        })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('student_id', 'like', "%{$request->search}%")
                        ->orWhere('first_name', 'like', "%{$request->search}%")
                        ->orWhere('last_name', 'like', "%{$request->search}%");
                });
            })
            ->with(['enrollments' => function ($q) {
                $q->orderBy('school_year_id', 'desc')
                    ->orderBy('semester_id', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(1);
            }]);

        $students = $studentsQuery->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(40)
            ->withQueryString();

        $activeEnrollments = StudentEnrollment::where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->get()
            ->keyBy('student_id');

        $previousEnrollments = StudentEnrollment::whereIn('student_id', $students->pluck('id'))
            ->where(function ($q) use ($activeSY, $activeSem) {
                $q->where('school_year_id', '<', $activeSY->id)
                    ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                        $q2->where('school_year_id', $activeSY->id)
                            ->where('semester_id', '<', $activeSem->id);
                    });
            })
            ->orderBy('school_year_id', 'desc')
            ->orderBy('semester_id', 'desc')
            ->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->first());

        foreach ($students as $student) {
            $latest = $student->enrollments->first() ?? null;
            $student->displayEnrollment = $latest ?? $previousEnrollments[$student->id] ?? null;
            $student->isAdvised = $student->displayEnrollment && $student->displayEnrollment->status !== 'NOT_ENROLLED';

            if ($student->displayEnrollment) {
                $student->displayEnrollment->financial_status = $student->displayEnrollment->financial_status
                    ?: $student->displayEnrollment->computeFinancialStatus();
            }
        }

        // sort the paginated collection so advised students appear first
        $students->setCollection(
            $students->getCollection()->sortByDesc('isAdvised')
        );

        $courses = Course::where('college_id', $collegeId)->get();
        $years = YearLevel::where('college_id', $collegeId)->get();
        $sections = Section::where('college_id', $collegeId)->get();

        return view('college.validate_students', compact(
            'students',
            'activeSY',
            'activeSem',
            'courses',
            'years',
            'sections',
            'activeEnrollments',
            'previousEnrollments'
        ));
    }



    public function store(Request $request, $studentId)
    {
        abort_unless(Auth::user()->isAssessor(), 403);
        $student = Student::findOrFail($studentId);
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $request->validate([
            "course_id.$studentId" => 'required|exists:courses,id',
            "year_level_id.$studentId" => 'required|exists:year_levels,id',
            "section_id.$studentId" => 'required|exists:sections,id',
        ]);

        $enrollment = StudentEnrollment::updateOrCreate(
            [
                'student_id'     => $student->id,
                'school_year_id' => $activeSY->id,
                'semester_id'    => $activeSem->id,
            ],
            [
                'college_id'    => $collegeId,
                'course_id'     => $request->course_id[$student->id],
                'year_level_id' => $request->year_level_id[$student->id],
                'section_id'    => $request->section_id[$student->id],
                'status'        => 'ENROLLED',
                'assessed_by'   => Auth::id(),
                'assessed_at'   => now(),
            ]
        );
        return back()->with('status', 'Student validated successfully.');
    }

    public function bulkValidate(Request $request)
    {
        abort_unless(Auth::user()->isAssessor(), 403);
        if (!$request->has('selected_students')) {
            return back(); // ignore if no students selected (could be a markPaid request)
        }
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $request->validate([
            'selected_students' => 'required|array',
            'selected_students.*' => 'exists:students,id',
        ]);

        foreach ($request->selected_students as $studentId) {

            $request->validate([
                "course_id.$studentId" => 'required|exists:courses,id',
                "year_level_id.$studentId" => 'required|exists:year_levels,id',
                "section_id.$studentId" => 'required|exists:sections,id',
            ]);

            StudentEnrollment::updateOrCreate(
                [
                    'student_id'     => $studentId,
                    'school_year_id' => $activeSY->id,
                    'semester_id'    => $activeSem->id,
                ],
                [
                    'college_id'     => $collegeId,
                    'course_id'      => $request->course_id[$studentId],
                    'year_level_id'  => $request->year_level_id[$studentId],
                    'section_id'     => $request->section_id[$studentId],
                    'status' => 'ENROLLED',
                    'assessed_by' => Auth::id(),
                    'assessed_at' => now(),
                ]
            );
        }

        return back()->with(
            'status',
            count($request->selected_students) . ' students validated successfully.'
        );
    }

    // public function template()
    // {
    //     $headers = ['#', 'student_id', 'Student Name', 'Course', 'Year Level', 'Section', 'Contact', 'Email'];
    //     $filename = 'student_import_template.xlsx';

    //     return Excel::download(new TemplateExport($headers), $filename);
    // }

    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'student_file' => 'required|file|mimes:xlsx,csv',
    //     ]);

    //     try {
    //         Excel::import(new StudentsImport, $request->file('student_file'));
    //         return back()->with('status', 'Student list imported successfully. Students remain unvalidated.');
    //     } catch (\Exception $e) {
    //         return back()->withErrors(['student_file' => 'Error importing file: '.$e->getMessage()]);
    //     }
    // }

    public function import(Request $request)
    {
        $request->validate([
            'student_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $importer = new StudentsImport();
        Excel::import($importer, $request->file('student_file'));

        $result = $importer->getResult();

        $message = "{$result['created']} new student(s) added, {$result['updated']} updated.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} row(s) skipped (student ID exists with a different last name).";
        }

        return redirect()->back()->with('import_success', $message)
            ->with('import_skipped', $result['skipped_rows']);
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'student_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $collegeId = Auth::user()->college_id;

        // Read file into collection using Maatwebsite (first sheet, with heading row)
        $collection = Excel::toCollection(new \App\Imports\StudentsImportPreview(), $request->file('student_file'));
        $rows = $collection->first() ?? collect();

        $existing_match    = [];   // student_id + last_name both match → will be updated
        $existing_mismatch = [];   // student_id matches but last_name differs → will be skipped
        $new_count         = 0;

        foreach ($rows as $row) {
            $row = collect($row)->mapWithKeys(fn($v, $k) => [strtolower(str_replace(' ', '_', $k)) => $v]);

            $studentId = trim($row['student_id'] ?? '');
            $lastName  = trim($row['last_name']  ?? '');

            if (!$studentId || !$lastName) {
                continue;
            }

            $existing = \App\Models\Student::where('student_id', $studentId)->first();

            if ($existing) {
                // Check if this student has an enrollment in the current college
                $inCollege = $existing->enrollments()
                    ->where('college_id', $collegeId)
                    ->exists();

                if (!$inCollege) {
                    // Student exists globally but not in this college, treat as new
                    $new_count++;
                    continue;
                }

                if (strtolower($existing->last_name) === strtolower($lastName)) {
                    $existing_match[] = [
                        'student_id' => $studentId,
                        'last_name'  => $lastName,
                        'first_name' => trim($row['first_name'] ?? ''),
                    ];
                } else {
                    $existing_mismatch[] = [
                        'student_id'     => $studentId,
                        'file_last_name' => $lastName,
                        'db_last_name'   => $existing->last_name,
                        'first_name'     => trim($row['first_name'] ?? ''),
                    ];
                }
            } else {
                $new_count++;
            }
        }

        return response()->json([
            'existing_match'    => $existing_match,
            'existing_mismatch' => $existing_mismatch,
            'new_count'         => $new_count,
        ]);
    }

    public function markPaid(Student $student)
    {
        abort_unless(Auth::user()->isStudentCoordinator(), 403);

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $enrollment = StudentEnrollment::where([
            'student_id' => $student->id,
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
        ])->firstOrFail();

        // no columns left to update; payment details are stored in payments table

        return back()->with('status', 'Payment marked as completed.');
    }

    public function clearForEnrollment(Student $student)
    {
        abort_unless(Auth::user()->isStudentCoordinator(), 403);

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        try {
            $clearanceAudit = null;

            DB::transaction(function () use ($student, $activeSY, $activeSem, &$clearanceAudit) {
                $enrollment = StudentEnrollment::where([
                    'student_id' => $student->id,
                    'school_year_id' => $activeSY->id,
                    'semester_id' => $activeSem->id,
                ])->lockForUpdate()->firstOrFail();

                $context = $this->resolveFinancialContext($student, $enrollment);

                if (! $context['can_clear']) {
                    throw new \RuntimeException('financial_not_clearable');
                }

                $enrollment->update([
                    'cleared_for_enrollment' => true,
                    'financial_status' => $context['workflow_financial_status'] ?? $context['financial_status'],
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                ]);

                $clearanceAudit = [
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'validated_by' => Auth::id(),
                    'financial_status' => $context['workflow_financial_status'] ?? $context['financial_status'],
                ];
            });

            if ($clearanceAudit) {
                Log::info('Student cleared for enrollment', $clearanceAudit);
            }
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'financial_not_clearable') {
                return back()->withErrors([
                    'financial_status' => 'Student is not financially clearable yet.'
                ]);
            }

            throw $e;
        }

        return back()->with('status', 'Student cleared for enrollment.');
    }

    public function issuePromissoryNote(Request $request, Student $student, PromissoryNoteIssuanceService $issuanceService)
    {
        abort_unless(Auth::user()->isStudentCoordinator(), 403);

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (! $activeSY || ! $activeSem) {
            return back()->withErrors([
                'promissory_note' => 'No active academic period is available for PN issuance.',
            ]);
        }

        $activeEnrollment = $this->getActiveEnrollment($student);

        if (! $activeEnrollment) {
            return back()->withErrors([
                'promissory_note' => 'No active enrollment was found for this student.',
            ]);
        }

        $dueDateCeiling = $this->resolvePromissoryNoteDueDateCeiling($activeEnrollment);
        $activeSemester = Semester::where('is_active', true)->first();

        $validated = $request->validate([
            'due_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'selected_fee_ids' => ['required', 'array', 'min:1'],
            'selected_fee_ids.*' => ['integer'],
        ]);

        $requestedDueDate = Carbon::parse($validated['due_date']);

        // Validate that due date is within the active semester
        if ($activeSemester) {
            $semesterStart = $activeSemester->actualStartDate() ?? $activeSemester->plannedStartDate();
            $semesterEnd = $activeSemester->effectiveEndDate();

            if ($semesterStart && $requestedDueDate->lessThan($semesterStart)) {
                return back()->withErrors([
                    'promissory_note' => 'Due date must be on or after ' . $semesterStart->toDateString() . ' (semester start).',
                ]);
            }

            if ($semesterEnd && $requestedDueDate->greaterThan($semesterEnd)) {
                return back()->withErrors([
                    'promissory_note' => 'Due date must be on or before ' . $semesterEnd->toDateString() . ' (semester end).',
                ]);
            }
        } elseif ($dueDateCeiling && $requestedDueDate->greaterThan($dueDateCeiling)) {
            return back()->withErrors([
                'promissory_note' => 'Due date must be on or before ' . $dueDateCeiling->toDateString() . '.',
            ]);
        }

        $context = $this->resolveFinancialContext($student);

        if (! ($context['can_issue_promissory_note'] ?? false)) {
            return back()->withErrors([
                'promissory_note' => 'This student is not eligible for a new promissory note.',
            ]);
        }

        try {
            $note = DB::transaction(function () use ($student, $issuanceService, $context, $validated, $requestedDueDate) {
                $enrollment = StudentEnrollment::where([
                    'student_id' => $student->id,
                    'school_year_id' => SchoolYear::where('is_active', true)->value('id'),
                    'semester_id' => Semester::where('is_active', true)->value('id'),
                ])->lockForUpdate()->firstOrFail();

                abort_unless(
                    $enrollment->college_id === Auth::user()->college_id,
                    403
                );

                $blockingStatuses = PromissoryNote::OPEN_STATUSES;

                $hasBlockingNote = PromissoryNote::where('student_id', $student->id)
                    ->whereIn('status', $blockingStatuses)
                    ->lockForUpdate()
                    ->exists();

                if ($hasBlockingNote) {
                    throw new \RuntimeException('Student already has an active or pending promissory note.');
                }

                $selectedFeeIds = collect($validated['selected_fee_ids'])
                    ->map(fn($feeId) => (int) $feeId)
                    ->unique()
                    ->values();

                $selectedFees = collect($context['fees'] ?? [])->filter(function ($fee) use ($selectedFeeIds) {
                    return $fee->requirement_level === 'mandatory'
                        && (! isset($fee->payments) || $fee->payments->isEmpty())
                        && $selectedFeeIds->contains((int) $fee->id);
                });

                if ($selectedFees->count() !== $selectedFeeIds->count()) {
                    throw new \RuntimeException('Select only unpaid mandatory fees available for this promissory note.');
                }

                if ($selectedFees->isEmpty()) {
                    throw new \RuntimeException('No fees were selected for the promissory note.');
                }

                return $issuanceService->issueNote(
                    $enrollment,
                    $selectedFees,
                    Auth::user(),
                    $requestedDueDate,
                    $validated['notes'] ?? null
                );
            });

            Log::info('Promissory note issued for student enrollment', [
                'promissory_note_id' => $note->id,
                'student_id' => $student->id,
                'enrollment_id' => $note->enrollment_id,
                'issued_by' => Auth::id(),
                'original_amount' => $note->original_amount,
            ]);
        } catch (\RuntimeException $exception) {
            return back()->withErrors([
                'promissory_note' => $exception->getMessage(),
            ]);
        } catch (\Throwable $throwable) {
            report($throwable);

            return back()->withErrors([
                'promissory_note' => 'Unable to issue the promissory note at this time.',
            ]);
        }

        return back()->with('status', 'Promissory note issued for student signature.');
    }

    public function getFeesForStudent(Student $student)
    {
        return response()->json($this->resolveFinancialContext($student));
    }

    private function resolveFinancialContext(Student $student, ?StudentEnrollment $enrollment = null): array
    {
        $collegeId = Auth::user()->college_id;
        $activeEnrollment = $enrollment ?? $this->getActiveEnrollment($student);

        if (! $collegeId || ! $activeEnrollment || $activeEnrollment->college_id !== $collegeId) {
            return [
                'fees' => [],
                'financial_status' => null,
                'workflow_financial_status' => null,
                'can_clear' => false,
                'all_mandatory_fees_paid' => false,
                'promissory_note' => null,
            ];
        }

        $fees = $this->getCollegeFeesForStudent($student, $collegeId);
        $mandatoryFees = $fees->where('requirement_level', 'mandatory');
        $allMandatoryFeesPaid = $mandatoryFees->isEmpty()
            || $mandatoryFees->every(fn($fee) => isset($fee->payments) && $fee->payments->isNotEmpty());
        $storedFinancialStatus = $activeEnrollment->financial_status ?? $activeEnrollment->computeFinancialStatus();
        $dueDateCeiling = $this->resolvePromissoryNoteDueDateCeiling($activeEnrollment);

        $defaultDueDate = now()->addDays(30);
        if ($dueDateCeiling && $defaultDueDate->greaterThan($dueDateCeiling)) {
            $defaultDueDate = $dueDateCeiling->copy();
        }

        $activePromissoryNote = PromissoryNote::where('student_id', $student->id)
            ->where('status', PromissoryNote::STATUS_ACTIVE)
            ->with(['fees:id,fee_name'])
            ->first();

        $blockedPromissoryNote = PromissoryNote::where('student_id', $student->id)
            ->whereIn('status', [
                PromissoryNote::STATUS_DEFAULT,
                PromissoryNote::STATUS_BAD_DEBT,
            ])
            ->where('remaining_balance', '>', 0)
            ->orderByDesc('id')
            ->first();

        $hasBlockingPromissoryNote = PromissoryNote::where('student_id', $student->id)
            ->whereIn('status', PromissoryNote::OPEN_STATUSES)
            ->exists();

        if ($blockedPromissoryNote) {
            $workflowFinancialStatus = $blockedPromissoryNote->status;
        } elseif ($activePromissoryNote) {
            $workflowFinancialStatus = StudentEnrollment::FINANCIAL_DEFERRED;
        } elseif ($allMandatoryFeesPaid) {
            $workflowFinancialStatus = StudentEnrollment::FINANCIAL_PAID;
        } elseif ($activeEnrollment->payments()->exists()) {
            $workflowFinancialStatus = StudentEnrollment::FINANCIAL_PARTIALLY_PAID;
        } else {
            $workflowFinancialStatus = StudentEnrollment::FINANCIAL_UNPAID;
        }

        $unpaidMandatoryFeeIds = $mandatoryFees
            ->reject(fn($fee) => isset($fee->payments) && $fee->payments->isNotEmpty())
            ->pluck('id')
            ->toArray();

        $coversAllUnpaidMandatory = true;
        if ($activePromissoryNote) {
            $activePromissoryNoteFeeIds = $activePromissoryNote->fees->pluck('id')->toArray();
            // Confirm the active promissory note covers every unpaid mandatory fee before allowing clearance.
            $coversAllUnpaidMandatory = empty(array_diff($unpaidMandatoryFeeIds, $activePromissoryNoteFeeIds));
        }

        // Get the active semester for context
        $activeSemester = Semester::with('schoolYear')->where('is_active', true)->first();
        $semesterStart = $activeSemester?->actualStartDate() ?? $activeSemester?->plannedStartDate();
        $semesterEnd = $activeSemester?->effectiveEndDate();

        return [
            'fees' => $fees,
            'financial_status' => $storedFinancialStatus,
            'workflow_financial_status' => $workflowFinancialStatus,
            'can_clear' => ! $blockedPromissoryNote && ($allMandatoryFeesPaid || ($activePromissoryNote && $coversAllUnpaidMandatory)),
            'can_issue_promissory_note' => ! $hasBlockingPromissoryNote && ! $blockedPromissoryNote && ! empty($unpaidMandatoryFeeIds),
            'all_mandatory_fees_paid' => $allMandatoryFeesPaid,
            'preview_defaults' => [
                'due_date' => $defaultDueDate->toDateString(),
                'due_date_min' => $semesterStart?->toDateString(),
                'due_date_max' => $semesterEnd?->toDateString(),
                'semester_start_date' => $semesterStart?->toDateString(),
                'semester_end_date' => $semesterEnd?->toDateString(),
                'notes' => '',
            ],
            'promissory_note' => $activePromissoryNote ? [
                'id' => $activePromissoryNote->id,
                'status' => $activePromissoryNote->status,
                'due_date' => $activePromissoryNote->due_date?->toDateString(),
                'remaining_balance' => $activePromissoryNote->remaining_balance,
                'original_amount' => $activePromissoryNote->original_amount,
                'fees' => $activePromissoryNote->fees->map(fn($fee) => [
                    'id' => $fee->id,
                    'name' => $fee->fee_name,
                    'amount_deferred' => $fee->pivot->amount_deferred,
                ])->values(),
            ] : null,
        ];
    }

    private function getCollegeFeesForStudent(Student $student, int $collegeId)
    {
        $collegeOrgs = \App\Models\Organization::where('college_id', $collegeId)->get();
        $collegeOrgIds = $collegeOrgs->pluck('id')->toArray();

        $allOrgIds = $collegeOrgIds;

        $motherOrgIds = $collegeOrgs
            ->whereNotNull('mother_organization_id')
            ->pluck('mother_organization_id')
            ->unique()
            ->toArray();

        if (! empty($motherOrgIds)) {
            $allOrgIds = array_merge($allOrgIds, $motherOrgIds);
        }

        if (! empty($motherOrgIds)) {
            $hasOSAInheritance = \App\Models\Organization::whereIn('id', $motherOrgIds)
                ->where('inherits_osa_fees', true)
                ->exists();

            if ($hasOSAInheritance) {
                $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
                if ($osaId) {
                    $allOrgIds[] = $osaId;
                }
            }
        }

        $allOrgIds = array_values(array_unique($allOrgIds));
 $activeSY = SchoolYear::where('is_active', true)->first();
$activeSem = Semester::where('is_active', true)->first();

        return \App\Models\Fee::where('status', 'APPROVED')
            ->where(function ($q) use ($allOrgIds, $collegeId) {
                $q->whereIn('organization_id', $allOrgIds)
                    ->orWhere(function ($q2) use ($collegeId) {
                        $q2->where('college_id', $collegeId)
                            ->whereNull('organization_id');
                    });
            })
            ->with([
                'organization',
                'payments' => fn($q) => $q ->where('student_id', $student->id) ->where('school_year_id', $activeSY->id) ->where('semester_id', $activeSem->id)
            ])
            ->get()
            ->unique('id')
            ->values();
    }

    private function getActiveEnrollment(Student $student): ?StudentEnrollment
    {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (! $activeSY || ! $activeSem) {
            return null;
        }

        return StudentEnrollment::where([
            'student_id' => $student->id,
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
        ])->first();
    }

    private function resolvePromissoryNoteDueDateCeiling(StudentEnrollment $enrollment): ?Carbon
    {
        // Get the CURRENTLY ACTIVE semester (not the enrolled semester)
        // PN due dates must fall within the active semester's effective end date,
        // which includes planned semester end dates introduced by the OSA model.
        $activeSemester = Semester::with('schoolYear')
            ->where('is_active', true)
            ->first();

        return $activeSemester?->effectiveEndDate();
    }
}
