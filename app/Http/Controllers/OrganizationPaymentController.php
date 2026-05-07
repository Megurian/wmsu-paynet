<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Fee;
use App\Models\Organization;
use App\Models\StudentEnrollment;
use App\Models\Payment;
use App\Models\PromissoryNote;
use App\Services\PromissoryNoteSettlementService;
use App\Http\Requests\CollectPromissoryPaymentRequest;
use App\Exceptions\PromissoryNoteException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationPaymentController extends Controller
{
    public function searchStudents(Request $request)
    {
        $query = $request->q;
        if (!$query) {
            return response()->json([]);
        }

        $user = Auth::user();
        if (! $user || ! $user->organization) {
            return response()->json([]);
        }

        $collegeId = $user->organization->college_id ?? $user->college_id ?? null;
        if (!$collegeId) {
            return response()->json([]);
        }

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json([]);
        }

        $students = Student::where(function ($studentQuery) use ($activeSY, $activeSem, $collegeId) {
            $studentQuery->whereHas('enrollments', function ($q) use ($activeSY, $activeSem, $collegeId) {
                $q->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED'])
                    ->where('school_year_id', $activeSY->id)
                    ->where('semester_id', $activeSem->id)
                    ->where('college_id', $collegeId);
            })
            ->orWhereHas('enrollments', function ($q) use ($collegeId) {
                $q->where('status', 'FOR_PAYMENT_VALIDATION')
                    ->where('is_void', false)
                    ->where('college_id', $collegeId);
            })
            ->orWhereHas('promissoryNotes', function ($q) use ($collegeId) {
                $q->whereIn('status', [
                        PromissoryNote::STATUS_DEFAULT,
                        PromissoryNote::STATUS_BAD_DEBT,
                    ])
                    ->where('remaining_balance', '>', 0)
                    ->whereHas('enrollment', function ($q2) use ($collegeId) {
                        $q2->where('college_id', $collegeId);
                    });
            });
        })
            ->where(function ($q) use ($query) {
                $q->where('student_id', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $enrollment = $s->enrollments->sortByDesc('id')->first();
                return [
                    'id' => $s->id,
                    'student_id' => $s->student_id,
                    'first_name' => $s->first_name,
                    'last_name' => $s->last_name,
                    'name' => trim("{$s->last_name}, {$s->first_name} {$s->middle_name}"),
                    'email' => $s->email,
                    'course' => $enrollment?->course?->name,
                    'year' => $enrollment?->yearLevel?->name,
                    'section' => $enrollment?->section?->name,
                ];
            });

        return response()->json($students);
    }

    public function getStudentFees($studentId)
    {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(['message' => 'No active school year or semester.'], 422);
        }

        $student = Student::findOrFail($studentId);
        $paymentEnrollment = $this->resolvePaymentEnrollment($student);

        // A student without an active current enrollment may still have an outstanding
        // defaulted/bad debt PN that should be settled by this organization.

        $user = Auth::user();
        if (! $user || ! $user->organization) {
            return response()->json(['message' => 'Organization not found.'], 422);
        }

        $userOrg = $user->organization;

        $activePromissoryNote = PromissoryNote::where('student_id', $student->id)
            ->whereIn('status', [
                PromissoryNote::STATUS_ACTIVE,
                PromissoryNote::STATUS_DEFAULT,
                PromissoryNote::STATUS_BAD_DEBT,
            ])
            ->where('remaining_balance', '>', 0)
            ->with('fees:id')
            ->orderByDesc('id')
            ->first();

        $pnFeeIds = $activePromissoryNote ? $activePromissoryNote->fees->pluck('id')->toArray() : [];

        $organizationIds = [$userOrg->id];

        $fees = collect([]);
        $paidFeeIds = [];

        if ($paymentEnrollment) {
            if ($userOrg->mother_organization_id) {
                $organizationIds[] = $userOrg->mother_organization_id;
            }

            if ($userOrg->motherOrganization?->inherits_osa_fees) {
                $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
                if ($osaId) {
                    $organizationIds[] = $osaId;
                }
            }

            $feesQuery = Fee::with(['createdSchoolYear', 'createdSemester'])
                ->whereIn('status', $paymentEnrollment->shouldIncludeDisabledFees() ? ['approved', 'disabled'] : ['approved'])
                ->where(function ($q) use ($organizationIds, $userOrg, $paymentEnrollment) {
                    $paymentPeriodCondition = function ($query) use ($paymentEnrollment) {
                        $query->where(function ($q2) use ($paymentEnrollment) {
                            $q2->whereNull('created_school_year_id')
                                ->orWhere('created_school_year_id', '<=', $paymentEnrollment->school_year_id);
                        })->where(function ($q2) use ($paymentEnrollment) {
                            $q2->whereNull('created_semester_id')
                                ->orWhere('created_semester_id', '<=', $paymentEnrollment->semester_id);
                        });
                    };

                    $q->where('organization_id', $userOrg->id)
                        ->where($paymentPeriodCondition);

                    $otherOrgIds = array_filter($organizationIds, fn ($id) => $id !== $userOrg->id);
                    if (!empty($otherOrgIds)) {
                        $q->orWhere(function ($q2) use ($otherOrgIds, $paymentPeriodCondition) {
                            $q2->whereIn('organization_id', $otherOrgIds)
                                ->where('fee_scope', '!=', 'college')
                                ->where($paymentPeriodCondition);
                        });
                    }
                });

            if (strtoupper(optional($paymentEnrollment->semester)->name) === 'SUMMER') {
                $feesQuery->where('recurrence', '!=', 'semestrial');
            }

            $feesQuery->orderBy('created_at', 'desc');

            if (!empty($pnFeeIds)) {
                $feesQuery->whereNotIn('id', $pnFeeIds);
            }

            $feesCollection = $feesQuery->get()->unique('id')->values();

            $paidFeeIds = \App\Models\Fee::paidFeeIdsForStudentByPeriod(
                $student->id,
                $feesCollection->pluck('id')->toArray(),
                $paymentEnrollment->school_year_id,
                $paymentEnrollment->semester_id
            );

            $fees = $feesCollection->reject(function ($fee) use ($paidFeeIds) {
                return $fee->recurrence === 'one_time' && in_array($fee->id, $paidFeeIds, true);
            })->values()->map(function ($fee) use ($paymentEnrollment) {
                return [
                    'id' => $fee->id,
                    'fee_name' => $fee->fee_name,
                    'amount' => $fee->amount,
                    'requirement_level' => $fee->requirement_level,
                    'school_year' => $paymentEnrollment->schoolYear ?
                        $paymentEnrollment->schoolYear->sy_start->format('Y') . ' - ' . $paymentEnrollment->schoolYear->sy_end->format('Y')
                        : null,
                    'semester' => $paymentEnrollment->semester?->name,
                ];
            });

            $paidFeeIds = array_values(array_filter($paidFeeIds, function ($feeId) use ($fees) {
                return $fees->contains('id', $feeId);
            }));
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'course' => $paymentEnrollment?->course?->name,
                'year' => $paymentEnrollment?->yearLevel?->name,
                'section' => $paymentEnrollment?->section?->name,
                'school_year' => $paymentEnrollment?->schoolYear ?
                    $paymentEnrollment->schoolYear->sy_start->format('Y') . ' - ' . $paymentEnrollment->schoolYear->sy_end->format('Y')
                    : null,
                'semester' => $paymentEnrollment?->semester?->name,
            ],
            'fees' => $fees,
            'paid_fee_ids' => $paidFeeIds
        ]);
    }


    public function collectPayment(Request $request)
    {
        // If promissory_note_id is provided, handle PN settlement
        if ($request->has('promissory_note_id') && $request->promissory_note_id) {
            return $this->collectPromissoryPayment($request);
        }

        // Otherwise, handle traditional cash payment
        return $this->collectCashPayment($request);
    }

    /**
     * Fetch active promissory note for a student (org-level context).
     * Returns 0 or 1 active PN per student.
     * 
     * GET /college_org/students/{student_id}/promissory-notes
     */
    public function getPromissoryNotes($studentId)
    {
        $user = Auth::user();
        if (! $user || ! $user->organization) {
            return response()->json(null);
        }

        $organization = $user->organization;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(null);
        }

        $student = Student::findOrFail($studentId);

        // Verify student belongs to this organization's college
        $allowedOrgIds = $this->getAllowedOrganizationIds($organization);

        $settleablePN = PromissoryNote::where('student_id', $student->id)
            ->whereIn('status', [
                PromissoryNote::STATUS_ACTIVE,
                PromissoryNote::STATUS_DEFAULT,
                PromissoryNote::STATUS_BAD_DEBT,
            ])
            ->where('remaining_balance', '>', 0)
            ->whereHas('enrollment', function ($q) use ($organization) {
                $q->where('college_id', $organization->college_id);
            });

        // Fetch the latest settleable PN (ACTIVE, DEFAULT, or BAD_DEBT)

        $settleablePN = $settleablePN->whereHas('fees', function ($q) use ($allowedOrgIds, $organization) {
                $q->where('organization_id', $organization->id);

                $otherOrgIds = array_filter($allowedOrgIds, fn ($id) => $id !== $organization->id);
                if (!empty($otherOrgIds)) {
                    $q->orWhere(function ($q2) use ($otherOrgIds) {
                        $q2->whereIn('organization_id', $otherOrgIds)
                            ->where('fee_scope', '!=', 'college');
                    });
                }
            })
            ->with(['fees' => function ($q) use ($allowedOrgIds, $organization) {
                $q->where('organization_id', $organization->id);

                $otherOrgIds = array_filter($allowedOrgIds, fn ($id) => $id !== $organization->id);
                if (!empty($otherOrgIds)) {
                    $q->orWhere(function ($q2) use ($otherOrgIds) {
                        $q2->whereIn('organization_id', $otherOrgIds)
                            ->where('fee_scope', '!=', 'college');
                    });
                }

                $q->select('fees.id', 'fees.fee_name');
            }])
            ->with(['enrollment.schoolYear', 'enrollment.semester'])
            ->orderByDesc('id')
            ->first();

        if (! $settleablePN) {
            return response()->json(null);
        }

        $feePayments = DB::table('fee_payment')
            ->join('payments', 'fee_payment.payment_id', '=', 'payments.id')
            ->where('payments.promissory_note_id', $settleablePN->id)
            ->groupBy('fee_payment.fee_id')
            ->select('fee_payment.fee_id', DB::raw('SUM(fee_payment.amount_paid) as amount_paid'))
            ->pluck('amount_paid', 'fee_id');

        $fees = $settleablePN->fees->map(function ($fee) use ($feePayments) {
            $amountDeferred = (float) $fee->pivot->amount_deferred;
            $amountPaid = (float) ($feePayments[$fee->id] ?? 0);
            $amountRemaining = max(0, $amountDeferred - $amountPaid);

            if ($amountRemaining <= 0) {
                return null;
            }

            return [
                'id' => $fee->id,
                'name' => $fee->fee_name,
                'amount_deferred' => $amountDeferred,
                'amount_paid' => $amountPaid,
                'amount_remaining' => $amountRemaining,
            ];
        })->filter()->values();

        if ($fees->isEmpty()) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $settleablePN->id,
            'student_id' => $settleablePN->student_id,
            'organization_id' => $organization->id,
            'original_amount' => $settleablePN->original_amount,
            'remaining_balance' => $settleablePN->remaining_balance,
            'due_date' => $settleablePN->due_date->toDateString(),
            'status' => $settleablePN->status,
            'school_year' => $settleablePN->enrollment->schoolYear ?
                $settleablePN->enrollment->schoolYear->sy_start->format('Y') . ' - ' . $settleablePN->enrollment->schoolYear->sy_end->format('Y')
                : null,
            'semester' => $settleablePN->enrollment->semester?->name,
            'fees' => $fees,
        ]);
    }

    public function getStudentPaymentHistory(Student $student)
    {
        $user = Auth::user();
        if (! $user || ! $user->organization) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $organization = $user->organization;
        $collegeId = $organization->college_id;

        $isSameCollegeStudent = $student->enrollments()
            ->where('college_id', $collegeId)
            ->exists();

        if (! $isSameCollegeStudent) {
            return response()->json(['message' => 'Student not found in your college.'], 404);
        }

        $payments = Payment::with(['fees', 'schoolYear', 'semester', 'collector'])
            ->where('student_id', $student->id)
            ->whereHas('enrollment', function ($query) use ($collegeId) {
                $query->where('college_id', $collegeId);
            })
            ->orderBy('school_year_id')
            ->orderBy('semester_id')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => trim("{$student->last_name}, {$student->first_name} {$student->middle_name}"),
            ],
            'payments' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'date' => $payment->created_at?->format('F d, Y H:i') ?? null,
                    'school_year' => $payment->schoolYear ?
                        $payment->schoolYear->sy_start->format('Y') . ' - ' . $payment->schoolYear->sy_end->format('Y')
                        : '—',
                    'semester' => $payment->semester?->name ?? '—',
                    'payment_type' => $payment->payment_type,
                    'collector_id' => $payment->collected_by,
                    'collector_name' => $payment->collector?->name
                        ?? trim((string) ($payment->collector?->first_name . ' ' . $payment->collector?->last_name))
                        ?: '—',
                    'amount_due' => (float) $payment->amount_due,
                    'cash_received' => (float) $payment->cash_received,
                    'change' => (float) $payment->change,
                    'fees' => $payment->fees->map(function ($fee) {
                        return [
                            'id' => $fee->id,
                            'name' => $fee->fee_name,
                            'amount_paid' => (float) $fee->pivot->amount_paid,
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }

    private function getAllowedOrganizationIds(Organization $organization): array
    {
        $organizationIds = [$organization->id];

        if ($organization->mother_organization_id) {
            $organizationIds[] = $organization->mother_organization_id;
        }

        if ($organization->motherOrganization?->inherits_osa_fees) {
            $osaId = Organization::where('org_code', 'OSA')->value('id');
            if ($osaId) {
                $organizationIds[] = $osaId;
            }
        }

        return array_values(array_unique($organizationIds));
    }

    /**
     * Resolve which enrollment a student is paying against.
     */
    private function resolvePaymentEnrollment(Student $student): ?StudentEnrollment
    {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (! $activeSY || ! $activeSem) {
            return null;
        }

        $activeEnrollment = StudentEnrollment::with(['course', 'yearLevel', 'section', 'schoolYear', 'semester'])
            ->where('student_id', $student->id)
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->whereIn('status', [StudentEnrollment::FOR_PAYMENT_VALIDATION, StudentEnrollment::ENROLLED])
            ->first();

        if ($activeEnrollment) {
            $previousEnrollment = StudentEnrollment::with(['course', 'yearLevel', 'section', 'schoolYear', 'semester'])
                ->where('student_id', $student->id)
                ->where('id', '<', $activeEnrollment->id)
                ->where('status', StudentEnrollment::FOR_PAYMENT_VALIDATION)
                ->where('is_void', false)
                ->orderBy('school_year_id', 'desc')
                ->orderBy('semester_id', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($previousEnrollment) {
                return $previousEnrollment;
            }

            return $activeEnrollment;
        }

        return StudentEnrollment::with(['course', 'yearLevel', 'section', 'schoolYear', 'semester'])
            ->where('student_id', $student->id)
            ->where('status', StudentEnrollment::FOR_PAYMENT_VALIDATION)
            ->where('is_void', false)
            ->latest('id')
            ->first();
    }

    /**
     * Collect traditional cash payment (extracted from original collectPayment)
     */
    private function collectCashPayment(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required|array|min:1',
            'fee_ids.*' => 'distinct|exists:fees,id',
            'cash_received' => 'required|numeric|min:0',
        ]);

        // Authenticate organization and get active periods
        $organization = $user->organization;

        if (!$organization) {
            return response()->json(['message' => 'Organization not found.'], 422);
        }

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(['message' => 'No active school year or semester.'], 422);
        }

        // Fetch student
        $student = Student::findOrFail($request->student_id);

        // Determine the correct enrollment this payment should be recorded against.
        $enrollment = $this->resolvePaymentEnrollment($student);
        if (! $enrollment) {
            return response()->json(['message' => 'Student enrollment for payment could not be determined.'], 422);
        }

        // Verify student belongs to the correct college
        if ($enrollment->college_id !== $organization->college_id) {
            return response()->json(['message' => 'Student does not belong to this organization.'], 403);
        }

        $outstandingPN = PromissoryNote::where('student_id', $student->id)
            ->whereIn('status', [
                PromissoryNote::STATUS_ACTIVE,
                PromissoryNote::STATUS_DEFAULT,
                PromissoryNote::STATUS_BAD_DEBT,
            ])
            ->where('remaining_balance', '>', 0)
            ->exists();

        if ($outstandingPN) {
            return response()->json([
                'message' => 'Regular payment is blocked while the student has an outstanding promissory note. Please settle the promissory note first.'
            ], 422);
        }

        if (!in_array($enrollment->status, [StudentEnrollment::FOR_PAYMENT_VALIDATION, StudentEnrollment::ENROLLED])) {
            return response()->json([
                'message' => "Student enrollment status '{$enrollment->status}' does not allow payment."
            ], 422);
        }

        $fees = Fee::whereIn('id', $request->fee_ids)->get();

        if ($fees->count() !== count($request->fee_ids)) {
            return response()->json(['message' => 'One or more fees do not exist.'], 422);
        }

        // Verify all fees belong to this organization or are inherited from mother org
        $organizationIds = [$organization->id];
        if ($organization->mother_organization_id) {
            $organizationIds[] = $organization->mother_organization_id;
        }

        // Special exception: allow OSA fees if mother org inherits them
        if ($organization->motherOrganization?->inherits_osa_fees) {
            $osaId = Organization::where('org_code', 'OSA')->value('id');
            if ($osaId) {
                $organizationIds[] = $osaId;
            }
        }

        $invalidFees = $fees->whereNotIn('organization_id', $organizationIds)->pluck('id')->toArray();
        if (!empty($invalidFees)) {
            return response()->json([
                'message' => 'One or more selected fees do not belong to your organization.'
            ], 403);
        }

        $collegeScopeFees = $fees->where('fee_scope', 'college')
            ->where('organization_id', '!=', $organization->id)
            ->pluck('id')
            ->toArray();

        if (!empty($collegeScopeFees)) {
            return response()->json([
                'message' => 'College-scoped fees from other organizations cannot be paid in the organization payment window.'
            ], 403);
        }

        $totalAmount = $fees->sum('amount');

        if ($request->cash_received < $totalAmount) {
            return response()->json(['message' => 'Cash received is less than total amount due.'], 422);
        }

        $change = $request->cash_received - $totalAmount;

        // Check for already-paid fees with recurrence rules.
        $alreadyPaid = \App\Models\Fee::paidFeeIdsForStudentByPeriod(
            $student->id,
            $request->fee_ids,
            $enrollment->school_year_id,
            $enrollment->semester_id
        );

        if (!empty($alreadyPaid)) {
            $dupeCount = count($alreadyPaid);
            return response()->json([
                'message' => "$dupeCount fee(s) already paid for this enrollment."
            ], 422);
        }

        $orgCode = $organization->org_code ?? 'GEN';
        $dateStr = now()->format('Ymd');

        $countToday = Payment::where('organization_id', $organization->id)
            ->whereDate('created_at', now())
            ->count();

        $sequenceNum = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
        $randomSuffix = strtoupper(str_pad(dechex(random_int(0, 4095)), 3, '0', STR_PAD_LEFT));

        $transactionId = "{$orgCode}-{$dateStr}-{$sequenceNum}-{$randomSuffix}";

        $payment = DB::transaction(function () use ($student, $enrollment, $organization, $transactionId, $totalAmount, $change, $request, $fees) {
            $payment = Payment::create([
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'organization_id' => $organization->id,
                'school_year_id' => $enrollment->school_year_id,
                'semester_id' => $enrollment->semester_id,
                'payment_type' => 'CASH',
                'amount_due' => $totalAmount,
                'cash_received' => $request->cash_received,
                'change' => $change,
                'collected_by' => Auth::id(),
                'transaction_id' => $transactionId,
            ]);

            foreach ($fees as $fee) {
                $payment->fees()->attach($fee->id, ['amount_paid' => $fee->amount]);
            }

            $enrollment->updatePaymentStatusForApplicableFees();

            return $payment;
        });

        return response()->json([
            'message' => 'Payment collected successfully.',
            'payment_id' => $payment->id,
            'transaction_id' => $transactionId,
            'amount_due' => $totalAmount,
            'change' => $change,
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => "{$student->first_name} {$student->last_name}"
            ]
        ]);
    }

    private function getApplicableFeesForEnrollment(StudentEnrollment $enrollment, Organization $organization)
    {
        $organizationIds = [$organization->id];

        if ($organization->mother_organization_id) {
            $organizationIds[] = $organization->mother_organization_id;
        }

        if ($organization->motherOrganization?->inherits_osa_fees) {
            $osaId = Organization::where('org_code', 'OSA')->value('id');
            if ($osaId) {
                $organizationIds[] = $osaId;
            }
        }

        $query = Fee::whereIn('status', $enrollment->shouldIncludeDisabledFees() ? ['approved', 'disabled'] : ['approved'])
            ->where(function ($q) use ($organizationIds, $organization, $enrollment) {
                $paymentPeriodCondition = function ($query) use ($enrollment) {
                    $query->where(function ($q2) use ($enrollment) {
                        $q2->whereNull('created_school_year_id')
                            ->orWhere('created_school_year_id', '<=', $enrollment->school_year_id);
                    })->where(function ($q2) use ($enrollment) {
                        $q2->whereNull('created_semester_id')
                            ->orWhere('created_semester_id', '<=', $enrollment->semester_id);
                    });
                };

                $q->where('organization_id', $organization->id)
                    ->where($paymentPeriodCondition);

                $otherOrgIds = array_filter($organizationIds, fn ($id) => $id !== $organization->id);
                if (!empty($otherOrgIds)) {
                    $q->orWhere(function ($q2) use ($otherOrgIds, $paymentPeriodCondition) {
                        $q2->whereIn('organization_id', $otherOrgIds)
                            ->where('fee_scope', '!=', 'college')
                            ->where($paymentPeriodCondition);
                    });
                }
            });

        if (strtoupper(optional($enrollment->semester)->name) === 'SUMMER') {
            $query->where('recurrence', '!=', 'semestrial');
        }

        return $query->orderBy('created_at', 'desc')
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * Collect promissory note settlement payment (org-level)
     */
    private function collectPromissoryPayment(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $rules = (new CollectPromissoryPaymentRequest())->rules();
        $messages = (new CollectPromissoryPaymentRequest())->messages();
        $request->validate($rules, $messages);

        try {
            $organization = $user->organization;

            if (!$organization) {
                return response()->json(['message' => 'Organization not found.'], 422);
            }

            $student = Student::findOrFail($request->student_id);
            $promissoryNote = PromissoryNote::findOrFail($request->promissory_note_id);

            // Verify PN belongs to this student
            if ($promissoryNote->student_id !== $student->id) {
                return response()->json([
                    'message' => 'Promissory note does not belong to this student.'
                ], 403);
            }

            // Verify enrollment belongs to this organization's college
            if (! $promissoryNote->enrollment || $promissoryNote->enrollment->college_id !== $organization->college_id) {
                return response()->json([
                    'message' => 'Promissory note does not belong to this college.'
                ], 403);
            }

            $collegeFeeSelected = Fee::whereIn('id', $request->selected_fees)
                ->where('fee_scope', 'college')
                ->where('organization_id', '!=', $organization->id)
                ->exists();

            if ($collegeFeeSelected) {
                return response()->json([
                    'message' => 'College-scoped fees from other organizations cannot be settled in the organization payment window.'
                ], 403);
            }

            // Use settlement service to process payment
            $settlementService = new PromissoryNoteSettlementService();

            $result = $settlementService->settlePayment(
                $promissoryNote,
                $request->cash_received,
                $request->selected_fees,
                $user,
                true // Is org payment
            );

            $promissoryNote->enrollment->updatePaymentStatusForApplicableFees();

            return response()->json([
                'message' => 'Promissory note payment collected successfully.',
                'payment_id' => $result['payment']->id,
                'transaction_id' => $result['transaction_id'],
                'promissory_note' => [
                    'id' => $promissoryNote->id,
                    'remaining_balance' => $result['remaining_balance'],
                    'status' => $promissoryNote->fresh()->status,
                    'is_closed' => $result['is_closed'],
                ],
                'change' => $result['payment']->change,
                'student' => [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'name' => "{$student->first_name} {$student->last_name}"
                ]
            ]);

        } catch (PromissoryNoteException $e) {
            Log::error('Organization PN settlement failed - business rule violation', [
                'student_id' => $request->student_id,
                'promissory_note_id' => $request->promissory_note_id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to process this payment. Please verify the amount and note status.',
            ], $e->getCode() ?: 422);
        } catch (\Exception $e) {
            Log::error('Organization PN settlement failed', [
                'student_id' => $request->student_id,
                'promissory_note_id' => $request->promissory_note_id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to process the payment at this time. Please try again later.',
            ], 500);
        }
    }

    public function records(Request $request)
    {
        $officerId = $request->input('officer_id');
        $user = Auth::user();
        abort_unless($user, 403);

        $organization = $user->organization;
        abort_unless($organization, 404);

        $collegeId = $organization->college_id;

        $activeSY = SchoolYear::where('is_active', true)->first() ?? SchoolYear::orderByDesc('sy_start')->first();
        $activeSYId = $activeSY?->id;

        $feeId = $request->input('fee_id');
        $feeRecurrence = $request->input('fee_recurrence');
        $requirementLevel = $request->input('requirement_level');
        $courseId = $request->input('course_id');
        $yearLevelId = $request->input('year_level_id');
        $sectionId = $request->input('section_id');

        $activeSem = $activeSY
            ? $activeSY->semesters()->where('is_active', true)->first() ?? $activeSY->semesters()->orderBy('id')->first()
            : Semester::orderByDesc('id')->first();
        $activeSemId = $activeSem?->id;

        $schoolYearId = $request->input('school_year_id', $activeSYId);
        $semesterId = $request->input('semester_id', $activeSemId);

        

        $students = Student::whereHas('enrollments', function ($q) use ($collegeId, $schoolYearId, $semesterId) {
            $q->where('college_id', $collegeId);

            if ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            }

            if ($semesterId) {
                $q->where('semester_id', $semesterId);
            }
        })
        ->with(['enrollments' => function ($q) use ($schoolYearId, $semesterId) {
            if ($schoolYearId) $q->where('school_year_id', $schoolYearId);
            if ($semesterId) $q->where('semester_id', $semesterId);
        }, 'enrollments.course', 'enrollments.yearLevel', 'enrollments.section'])
        ->get();

       $paymentsQuery = Payment::with([
        'student',
        'fees',
        'enrollment.course',
        'enrollment.yearLevel',
        'enrollment.section',
        'collector'
    ])
    ->where('organization_id', $organization->id)
    ->where('school_year_id', $schoolYearId)
    ->where('semester_id', $semesterId);

    $today = now()->endOfDay();
    $paymentsQuery->where('created_at', '<=', $today);

    if ($request->filled('date_from')) {
        $paymentsQuery->where('created_at', '>=', \Carbon\Carbon::parse($request->date_from)->startOfDay());
    }

    if ($request->filled('date_to')) {
        $paymentsQuery->where('created_at', '<=', \Carbon\Carbon::parse($request->date_to)->endOfDay());
    }

    if ($request->filled('search')) {
        $query = $request->input('search');
        $paymentsQuery->whereHas('student', function ($q) use ($query) {
            $q->where('student_id', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
        });
    }

    if ($officerId) {
        $paymentsQuery->where('collected_by', $officerId);
    }

    if ($courseId) {
        $paymentsQuery->whereHas('enrollment.course', fn ($q) =>
            $q->where('id', $courseId)
        );
    }


    if ($yearLevelId) {
        $paymentsQuery->whereHas('enrollment.yearLevel', fn ($q) =>
            $q->where('id', $yearLevelId)
        );
    }

    if ($sectionId) {
        $paymentsQuery->whereHas('enrollment.section', fn ($q) =>
            $q->where('id', $sectionId)
        );
    }


        $payments = $paymentsQuery->latest()->get();

        $studentsWithPayments = [];

        foreach ($students as $student) {
            $enrollment = $student->enrollments->first();
            if (!$enrollment) continue;

            $studentPayments = $payments->where('student_id', $student->id);

            foreach ($studentPayments as $payment) {
                foreach ($payment->fees as $fee) {
                    if ($feeId && $fee->id != $feeId) {
                        continue;
                    }

                    if ($requirementLevel && $fee->requirement_level !== $requirementLevel) {
                        continue;
                    }
                    $studentsWithPayments[] = [
                        'student' => $student,
                        'enrollment' => $enrollment,
                        'fee' => $fee,
                        'status' => 'Paid',
                        'amount' => $payment->pivot->amount_paid ?? $fee->amount,
                        'payment_date' => $payment->created_at,
                        'collector' => $payment->collector,
                    ];
                }
            }

            $organizationIds = [$organization->id];
            if ($organization->mother_organization_id) $organizationIds[] = $organization->mother_organization_id;
            if ($organization->motherOrganization?->inherits_osa_fees) {
                $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
                if ($osaId) $organizationIds[] = $osaId;
            }

           $allFeesQuery = Fee::where('status', 'approved')
                ->whereIn('organization_id', $organizationIds);

            if ($feeId) {
                $allFeesQuery->where('id', $feeId);
            }

            $allFees = $allFeesQuery->get();        
             $paidFeeIds = $studentPayments->pluck('fees.*.id')->flatten()->unique()->toArray();
            $pendingFees = $allFees->whereNotIn('id', $paidFeeIds)->values();

            foreach ($pendingFees as $fee) {
                if ($requirementLevel && $fee->requirement_level !== $requirementLevel) {
                    continue;
                }

                if ($feeRecurrence && $fee->recurrence !== $feeRecurrence) {
                    continue;
                }
                $studentsWithPayments[] = [
                    'student' => $student,
                    'enrollment' => $enrollment,
                    'fee' => $fee,
                    'status' => 'Pending',
                    'amount' => 0,
                    'payment_date' => null,
                ];
            }
        }

        $studentsWithPayments = collect($studentsWithPayments);

        $status = $request->input('payment_status');

        if (!$request->filled('payment_status')) {
            $studentsWithPayments = $studentsWithPayments
                ->where('status', 'Paid')
                ->values();
        }

        if ($status === 'paid') {
            $studentsWithPayments = $studentsWithPayments
                ->where('status', 'Paid')
                ->values();
        }

        if ($status === 'pending') {
            $studentsWithPayments = $studentsWithPayments
                ->where('status', 'Pending')
                ->values();
        }

    $filteredPayments = $payments->filter(function ($payment) use ($feeId, $requirementLevel) {

        return $payment->fees->contains(function ($fee) use ($feeId, $requirementLevel) {

            if ($feeId && $fee->id != $feeId) return false;

            if ($requirementLevel && $fee->requirement_level !== $requirementLevel) {
                return false;
            }

            return true;
        });
    });
    $totalTransactions = $filteredPayments->count();

    $totalCollected = $filteredPayments->sum(function ($payment) use ($feeId, $requirementLevel) {
        return $payment->fees->sum(function ($fee) use ($feeId, $requirementLevel) {

            if ($feeId && $fee->id != $feeId) return 0;
            if ($requirementLevel && $fee->requirement_level !== $requirementLevel) return 0;

            return $fee->pivot->amount_paid ?? $fee->amount;
        });
    });

    $todayCollections = $filteredPayments
        ->filter(fn ($p) => $p->created_at->isToday())
        ->sum(function ($payment) use ($feeId, $requirementLevel) {
            return $payment->fees->sum(function ($fee) use ($feeId, $requirementLevel) {

                if ($feeId && $fee->id != $feeId) return 0;
                if ($requirementLevel && $fee->requirement_level !== $requirementLevel) return 0;

                return $fee->pivot->amount_paid ?? $fee->amount;
            });
        });

    $mandatoryCollected = $filteredPayments->sum(function ($payment) use ($feeId) {
        return $payment->fees->sum(function ($fee) use ($feeId) {

            if ($feeId && $fee->id != $feeId) return 0;
            if ($fee->requirement_level !== 'mandatory') return 0;

            return $fee->pivot->amount_paid ?? $fee->amount;
        });
    });

    $optionalCollected = $filteredPayments->sum(function ($payment) use ($feeId) {
        return $payment->fees->sum(function ($fee) use ($feeId) {

            if ($feeId && $fee->id != $feeId) return 0;
            if ($fee->requirement_level !== 'optional') return 0;

            return $fee->pivot->amount_paid ?? $fee->amount;
        });
    });

        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters = Semester::orderBy('id')->get();
        $organizationIds = [$organization->id];
        if ($organization->mother_organization_id) {
            $organizationIds[] = $organization->mother_organization_id;
        }
        if ($organization->motherOrganization?->inherits_osa_fees) {
            $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
            if ($osaId) $organizationIds[] = $osaId;
        }

        $fees = Fee::where('status', 'approved')
            ->whereIn('organization_id', $organizationIds);

        if ($schoolYearId !== null) {
            $fees->where(function ($q) use ($schoolYearId) {
                $q->whereNull('created_school_year_id')
                    ->orWhere('created_school_year_id', '<=', $schoolYearId);
            });
        }

        if ($semesterId !== null) {
            $fees->where(function ($q) use ($semesterId) {
                $q->whereNull('created_semester_id')
                    ->orWhere('created_semester_id', '<=', $semesterId);
            });
        }

        $fees = $fees->orderBy('created_at', 'desc')->get();
        $courses = \App\Models\Course::where('college_id', $collegeId)->get();
        $yearLevels = \App\Models\YearLevel::where('college_id', $collegeId)->get();
        $sections = \App\Models\Section::where('college_id', $collegeId)->get();

        $officers = \App\Models\OrganizationOfficer::with('user')
        ->where('organization_id', $organization->id)
        ->where('is_active', true)
        ->get();

        return view('college_org.records', compact(
            'studentsWithPayments', // 
            'schoolYears',
            'semesters',
            'fees',
            'courses',
            'yearLevels',
            'sections',
            'schoolYearId',
            'semesterId',
            'totalTransactions',
            'totalCollected',
            'todayCollections',
            'mandatoryCollected',
            'optionalCollected',
           'officers'
        ));
    }

   public function generateReport(Request $request)
{
    $user = Auth::user();
    abort_unless($user, 403);

    $organization = $user->organization;
    abort_unless($organization, 404);

    $collegeId = $organization->college_id;

    $activeSY = SchoolYear::where('is_active', true)->first()
        ?? SchoolYear::orderByDesc('sy_start')->first();

    $activeSem = Semester::where('is_active', true)->first()
        ?? Semester::orderBy('id')->first();

    $schoolYearId = $request->input('school_year_id', $activeSY?->id);
    $semesterId   = $request->input('semester_id', $activeSem?->id);

    $dateFrom = $request->filled('date_from')
        ? \Carbon\Carbon::parse($request->date_from)->startOfDay()
        : null;

    $dateTo = $request->filled('date_to')
        ? \Carbon\Carbon::parse($request->date_to)->endOfDay()
        : now()->endOfDay();

    $students = Student::whereHas('enrollments', function ($q) use ($collegeId, $schoolYearId, $semesterId) {
        $q->where('college_id', $collegeId)
            ->where('school_year_id', $schoolYearId)
            ->where('semester_id', $semesterId);
    })
    ->with(['enrollments' => function ($q) use ($schoolYearId, $semesterId) {
        $q->where('school_year_id', $schoolYearId)
            ->where('semester_id', $semesterId)
            ->with(['course', 'yearLevel', 'section']);
    }])
    ->get();

    $paymentsQuery = Payment::with([
            'fees',
            'student',
            'enrollment.course',
            'enrollment.yearLevel',
            'enrollment.section',
            'collector'
        ])
        ->where('organization_id', $organization->id)
        ->where('school_year_id', $schoolYearId)
        ->where('semester_id', $semesterId);

    if ($dateFrom) {
        $paymentsQuery->where('created_at', '>=', $dateFrom);
    }

    if ($dateTo) {
        $paymentsQuery->where('created_at', '<=', $dateTo);
    }

    if ($request->filled('course_id')) {
        $paymentsQuery->whereHas('enrollment.course', fn ($q) =>
            $q->where('id', $request->course_id)
        );
    }

    if ($request->filled('year_level_id')) {
        $paymentsQuery->whereHas('enrollment.yearLevel', fn ($q) =>
            $q->where('id', $request->year_level_id)
        );
    }

    if ($request->filled('section_id')) {
        $paymentsQuery->whereHas('enrollment.section', fn ($q) =>
            $q->where('id', $request->section_id)
        );
    }

    if ($request->filled('search')) {
        $query = $request->search;

        $paymentsQuery->whereHas('student', function ($q) use ($query) {
            $q->where('student_id', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
        });
    }

    if ($request->filled('officer_id')) {
        $paymentsQuery->where('collected_by', $request->officer_id);
    }

    $payments = $paymentsQuery->get();

    $feeFilter = function ($fee) use ($request) {

        if ($request->filled('fee_id') && $fee->id != $request->fee_id) {
            return false;
        }

        if ($request->filled('requirement_level') && $fee->requirement_level != $request->requirement_level) {
            return false;
        }

        if ($request->filled('fee_recurrence') && $fee->recurrence != $request->fee_recurrence) {
            return false;
        }

        return true;
    };

    $results = [];

    foreach ($students as $student) {

        $enrollment = $student->enrollments->first();
        if (! $enrollment) continue;

        $studentPayments = $payments->where('student_id', $student->id);

        foreach ($studentPayments as $payment) {

            foreach ($payment->fees->filter($feeFilter) as $fee) {

                $results[] = [
                    'student' => $student,
                    'enrollment' => $enrollment,
                    'fee' => $fee,
                    'status' => 'Paid',
                    'amount' => $fee->pivot->amount_paid ?? $fee->amount,
                    'payment_date' => $payment->created_at,
                    'collector' => $payment->collector,
                ];
            }
        }

        $organizationIds = [$organization->id];

        if ($organization->mother_organization_id) {
            $organizationIds[] = $organization->mother_organization_id;
        }

        if ($organization->motherOrganization?->inherits_osa_fees) {
            $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
            if ($osaId) $organizationIds[] = $osaId;
        }

        $allFees = Fee::where('status', 'approved')
            ->whereIn('organization_id', $organizationIds)
            ->when($request->filled('fee_id'), fn ($q) =>
                $q->where('id', $request->fee_id)
            )
            ->when($request->filled('requirement_level'), fn ($q) =>
                $q->where('requirement_level', $request->requirement_level)
            )
            ->when($request->filled('fee_recurrence'), fn ($q) =>
                $q->where('recurrence', $request->fee_recurrence)
            )
            ->get();

        $paidFeeIds = $studentPayments
            ->pluck('fees.*.id')
            ->flatten()
            ->unique()
            ->toArray();

        $pendingFees = $allFees->whereNotIn('id', $paidFeeIds);

        foreach ($pendingFees->filter($feeFilter) as $fee) {

            $results[] = [
                'student' => $student,
                'enrollment' => $enrollment,
                'fee' => $fee,
                'status' => 'Pending',
                'amount' => 0,
                'payment_date' => null,
                'collector' => null,
            ];
        }
    }

    $studentsWithPayments = collect($results);

    if (!$request->filled('payment_status')) {
        $studentsWithPayments = $studentsWithPayments->where('status', 'Paid')->values();
    }

    if ($request->payment_status === 'paid') {
        $studentsWithPayments = $studentsWithPayments->where('status', 'Paid')->values();
    }

    if ($request->payment_status === 'pending') {
        $studentsWithPayments = $studentsWithPayments->where('status', 'Pending')->values();
    }

    if ($request->format === 'pdf') {
        $html = view('college_org.reports.payment_report_pdf', compact('studentsWithPayments'))->render();

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('payment-report.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="payment-report.pdf"'
            ]
        );
    }

    if ($request->format === 'excel') {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PaymentReportExport($studentsWithPayments),
            'payment-report.xlsx'
        );
    }

    abort(404);
}
}
