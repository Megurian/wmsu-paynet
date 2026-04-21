<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Fee;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\College;
use App\Models\Payment;
use App\Models\PromissoryNote;
use App\Services\PromissoryNoteSettlementService;
use App\Http\Requests\CollectPromissoryPaymentRequest;
use App\Exceptions\PromissoryNoteException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TreasurerCashieringController extends Controller
{
    public function index() {
        $user = Auth::user();
        abort_unless($user, 403);

        $fees = Fee::where('status', 'approved')
            ->where('fee_scope', 'college')
            ->where('college_id', $user->college_id)
            ->whereNull('organization_id')
            ->whereHas('creator', function ($q) {
                $q->whereJsonContains('role', 'student_coordinator');
            })
            ->get();

        return view('college.cashiering', compact('fees'));
    }

    public function searchAdvisedStudents(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $query = $request->query('q');
        $collegeId = $user->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json([]);
        }

        $students = Student::whereHas('enrollments', function($q) use ($activeSY, $activeSem, $collegeId) {
            $q->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED'])
              ->where('school_year_id', $activeSY->id)
              ->where('semester_id', $activeSem->id)
              ->where('college_id', $collegeId);
        })
        ->where(function($q) use ($query) {
            $q->where('student_id', 'like', "%$query%")
              ->orWhere('first_name', 'like', "%$query%")
              ->orWhere('last_name', 'like', "%$query%");
        })
        ->take(10)
        ->get(['id','student_id','first_name','last_name']);

        return response()->json($students);
    }

    public function getStudentDetails($studentId)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $collegeId = $user->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(['message' => 'No active school year or semester.'], 422);
        }

        $student = Student::findOrFail($studentId);
        $enrollment = $this->resolvePaymentEnrollment($student);

        if (! $enrollment) {
            return response()->json(['message' => 'Student enrollment for payment could not be determined.'], 404);
        }

        // Verify student belongs to treasurer's college
        if ($enrollment->college_id !== $collegeId) {
            return response()->json(['message' => 'Student does not belong to this college.'], 403);
        }

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

        $feesQuery = Fee::where('status', 'approved')
            ->where('fee_scope', 'college')
            ->where('college_id', $collegeId)
            ->whereNull('organization_id')
            ->whereHas('creator', function ($q) {
                $q->whereJsonContains('role', 'student_coordinator');
            });

        if (!empty($pnFeeIds)) {
            $feesQuery->whereNotIn('id', $pnFeeIds);
        }

        $fees = $feesQuery->get()->map(function ($fee) use ($enrollment) {
            return [
                'id' => $fee->id,
                'fee_name' => $fee->fee_name,
                'amount' => $fee->amount,
                'requirement_level' => $fee->requirement_level,
                'school_year' => optional($enrollment->schoolYear)->sy_start ? optional($enrollment->schoolYear)->sy_start->format('Y') . ' - ' . optional($enrollment->schoolYear)->sy_end->format('Y') : null,
                'semester' => optional($enrollment->semester)->name,
            ];
        });

        $paidFeeIds = DB::table('fee_payment')
            ->join('payments', 'fee_payment.payment_id', '=', 'payments.id')
            ->where('payments.enrollment_id', $enrollment->id)
            ->pluck('fee_payment.fee_id')
            ->toArray();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'course' => optional($enrollment->course)->name,
                'year_level' => optional($enrollment->yearLevel)->name,
                'section' => optional($enrollment->section)->name,
                'school_year' => optional($enrollment->schoolYear)->sy_start ? optional($enrollment->schoolYear)->sy_start->format('Y') . ' - ' . optional($enrollment->schoolYear)->sy_end->format('Y') : null,
                'semester' => optional($enrollment->semester)->name,
            ],
            'fees' => $fees,
            'paid_fee_ids' => $paidFeeIds
        ]);
    }

    /**
     * Fetch active promissory note for a student (college-level context).
     * Returns 0 or 1 active PN per student.
     * 
     * GET /treasurer/cashiering/student/{student_id}/promissory-notes
     */
    public function getPromissoryNotes($studentId)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $collegeId = $user->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(null);
        }

        $student = Student::findOrFail($studentId);

        // Fetch settleable PN (ACTIVE, DEFAULT, or BAD_DEBT with balance remaining)
        // Only include promissory fees that belong to this college.
        $settleablePN = PromissoryNote::where('student_id', $student->id)
            ->whereIn('status', [
                PromissoryNote::STATUS_ACTIVE,
                PromissoryNote::STATUS_DEFAULT,
                PromissoryNote::STATUS_BAD_DEBT,
            ])
            ->where('remaining_balance', '>', 0)
            ->whereHas('fees', function ($q) use ($collegeId) {
                $q->where('fee_scope', 'college')
                  ->where('college_id', $collegeId)
                  ->whereNull('organization_id')
                  ->whereHas('creator', function ($q2) {
                      $q2->whereJsonContains('role', 'student_coordinator');
                  });
            })
            ->with(['fees' => function ($q) use ($collegeId) {
                $q->where('fee_scope', 'college')
                  ->where('college_id', $collegeId)
                  ->whereNull('organization_id')
                  ->whereHas('creator', function ($q2) {
                      $q2->whereJsonContains('role', 'student_coordinator');
                  })
                  ->select('fees.id', 'fees.fee_name');
            }])
            ->orderByDesc('id')
            ->first();

        if (!$settleablePN) {
            return response()->json(null);
        }

        if (! $settleablePN || ! $settleablePN->enrollment || $settleablePN->enrollment->college_id !== $collegeId) {
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

        // Format response with fee details
        return response()->json([
            'id' => $settleablePN->id,
            'student_id' => $settleablePN->student_id,
            'original_amount' => $settleablePN->original_amount,
            'remaining_balance' => $settleablePN->remaining_balance,
            'due_date' => $settleablePN->due_date->toDateString(),
            'status' => $settleablePN->status,
            'fees' => $fees,
        ]);
    }

    /**
     * Collect payment - handles both cash-only and promissory note settlement.
     * 
     * If promissory_note_id is provided in request, routes to PN settlement.
     * Otherwise, routes to traditional cash collection.
     * 
     * POST /treasurer/cashiering/collect
     */
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
     * Collect traditional cash payment (unchanged from original)
     */
    private function collectCashPayment(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required|array|min:1',
            'fee_ids.*' => 'distinct|exists:fees,id',
            'cash_received' => 'required|numeric|min:0',
        ]);

        $collegeId = $user->college_id;

        if (!$collegeId) {
            return response()->json(['message' => 'College not found for user.'], 422);
        }

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(['message' => 'No active school year or semester.'], 422);
        }

        $student = Student::findOrFail($request->student_id);

        $enrollment = $this->resolvePaymentEnrollment($student);

        if (! $enrollment) {
            return response()->json(['message' => 'Student enrollment for payment could not be determined.'], 422);
        }

        // Verify student belongs to this college
        if ($enrollment->college_id !== $collegeId) {
            return response()->json(['message' => 'Student does not belong to this college.'], 403);
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

        $fees = Fee::whereIn('id', $request->fee_ids)
            ->where('fee_scope', 'college')
            ->where('college_id', $collegeId)
            ->whereNull('organization_id')
            ->whereHas('creator', function ($q) {
                $q->whereJsonContains('role', 'student_coordinator');
            })
            ->get();

        if ($fees->count() !== count($request->fee_ids)) {
            return response()->json(['message' => 'One or more fees do not exist or are not valid for college-level cashiering.'], 422);
        }

        // Admin collects only college-level fees (no org fees)
        $invalidFees = [];

        if (!empty($invalidFees)) {
            return response()->json([
                'message' => 'One or more selected fees are not valid for college-level collection.'
            ], 422);
        }

        $totalAmount = $fees->sum('amount');

        if ($request->cash_received < $totalAmount) {
            return response()->json(['message' => 'Cash received is less than total amount due.'], 422);
        }

        $change = $request->cash_received - $totalAmount;

        // Check for already-paid fees
        $alreadyPaid = DB::table('fee_payment')
            ->join('payments', 'fee_payment.payment_id', '=', 'payments.id')
            ->where('payments.enrollment_id', $enrollment->id)
            ->whereIn('fee_payment.fee_id', $request->fee_ids)
            ->pluck('fee_payment.fee_id')
            ->toArray();

        if (!empty($alreadyPaid)) {
            $dupeCount = count($alreadyPaid);
            return response()->json([
                'message' => "$dupeCount fee(s) already paid for this enrollment."
            ], 422);
        }

        $college = College::find($collegeId);
        $collegeCode = optional($college)->college_code ? optional($college)->college_code . '-TRES' : 'UNK-TRES';
        $dateStr = now()->format('Ymd');

        $countToday = Payment::whereHas('enrollment', function ($q) use ($collegeId) {
                $q->where('college_id', $collegeId);
            })
            ->whereDate('created_at', now())
            ->count();

        $sequenceNum = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
        $randomSuffix = strtoupper(str_pad(dechex(random_int(0, 4095)), 3, '0', STR_PAD_LEFT));

        $transactionId = "{$collegeCode}-{$dateStr}-{$sequenceNum}-{$randomSuffix}";

        $payment = Payment::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'organization_id' => null,
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

        $applicableFees = $this->getApplicableFeesForEnrollment($enrollment, $collegeId);
        $enrollment->updatePaymentStatusForApplicableFees($applicableFees);

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

    private function getApplicableFeesForEnrollment(StudentEnrollment $enrollment, int $collegeId)
    {
        return Fee::where('status', 'approved')
            ->where('fee_scope', 'college')
            ->where('college_id', $collegeId)
            ->whereNull('organization_id')
            ->whereHas('creator', function ($q) {
                $q->whereJsonContains('role', 'student_coordinator');
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Collect promissory note settlement payment
     */
    private function collectPromissoryPayment(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $rules = (new CollectPromissoryPaymentRequest())->rules();
        $messages = (new CollectPromissoryPaymentRequest())->messages();
        $request->validate($rules, $messages);

        try {
            $collegeId = $user->college_id;

            if (!$collegeId) {
                return response()->json(['message' => 'College not found for user.'], 422);
            }

            $student = Student::findOrFail($request->student_id);
            $promissoryNote = PromissoryNote::findOrFail($request->promissory_note_id);

            // Verify PN belongs to this student
            if ($promissoryNote->student_id !== $student->id) {
                return response()->json([
                    'message' => 'Promissory note does not belong to this student.'
                ], 403);
            }

            // Verify enrollment belongs to this college
            if (! $promissoryNote->enrollment || $promissoryNote->enrollment->college_id !== $collegeId) {
                return response()->json([
                    'message' => 'Promissory note does not belong to this college.'
                ], 403);
            }

            $collegeFeeIds = Fee::whereIn('id', $request->selected_fees)
                ->where('fee_scope', 'college')
                ->where('college_id', $collegeId)
                ->whereNull('organization_id')
                ->whereHas('creator', function ($q) {
                    $q->whereJsonContains('role', 'student_coordinator');
                })
                ->pluck('id')
                ->toArray();

                if (count($collegeFeeIds) !== count($request->selected_fees)) {
                return response()->json([
                    'message' => 'Only college fees created by the student coordinator can be settled here.'
                ], 403);
            }

            // Use settlement service to process payment
            $settlementService = new PromissoryNoteSettlementService();

            $result = $settlementService->settlePayment(
                $promissoryNote,
                $request->cash_received,
                $request->selected_fees,
                $user,
                false // Not an org payment
            );

            $applicableFees = $this->getApplicableFeesForEnrollment($promissoryNote->enrollment, $collegeId);
            $promissoryNote->enrollment->updatePaymentStatusForApplicableFees($applicableFees);

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
            Log::warning('Treasurer PN settlement business exception', [
                'student_id' => $request->student_id,
                'promissory_note_id' => $request->promissory_note_id,
                'exception' => $e->getMessage(),
                'exception_type' => class_basename($e),
            ]);

            return response()->json([
                'message' => 'Unable to process this payment. Please verify the amount and note status.',
            ], $e->getCode() ?: 422);
        } catch (\Exception $e) {
            Log::error('Treasurer PN settlement failed', [
                'student_id' => $request->student_id,
                'promissory_note_id' => $request->promissory_note_id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to process the payment at this time. Please try again later.',
            ], 500);
        }
    }

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
                ->orderByDesc('id')
                ->first();

            if ($previousEnrollment && !$previousEnrollment->is_void && $previousEnrollment->status === StudentEnrollment::FOR_PAYMENT_VALIDATION) {
                return $previousEnrollment;
            }

            return $activeEnrollment;
        }

        return StudentEnrollment::with(['course', 'yearLevel', 'section', 'schoolYear', 'semester'])
            ->where('student_id', $student->id)
            ->where('status', StudentEnrollment::FOR_PAYMENT_VALIDATION)
            ->latest('id')
            ->first();
    }

    // public function downloadReceipt($paymentId)
    // {
    //     $payment = Payment::with(['student', 'fees', 'collector'])->findOrFail($paymentId);
    //
    //     $html = view('college.receipt-pdf', compact('payment'))->render();
    //
    //     $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
    //     $mpdf->WriteHTML($html);
    //
    //     return $mpdf->Output("receipt-{$payment->transaction_id}.pdf", 'I');
    // }
}