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
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $fees = Fee::where('status', 'APPROVED')
            ->where('fee_scope', 'college')
            ->where('college_id', Auth::user()->college_id)
            ->whereNull('organization_id')
            ->get();

        return view('college.cashiering', compact('fees'));
    }

    public function searchAdvisedStudents(Request $request)
    {
        $query = $request->query('q');
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

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
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(['message' => 'No active school year or semester.'], 422);
        }

        $student = Student::with(['enrollments' => function($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id);
        }])->findOrFail($studentId);

        $enrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Student not enrolled in active period.'], 404);
        }

        // Verify student belongs to treasurer's college
        if ($enrollment->college_id !== $collegeId) {
            return response()->json(['message' => 'Student does not belong to this college.'], 403);
        }

        $fees = Fee::where('status', 'APPROVED')
            ->where('fee_scope', 'college')
            ->where('college_id', $collegeId)
            ->whereNull('organization_id')
            ->get();

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
                'course' => $enrollment?->course->name ?? null,
                'year_level' => $enrollment?->yearLevel->name ?? null,
                'section' => $enrollment?->section->name ?? null,
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
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(null);
        }

        $student = Student::findOrFail($studentId);

        // Verify student belongs to this college
        $enrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->first();

        if (!$enrollment || $enrollment->college_id !== $collegeId) {
            return response()->json(null);
        }

        // Fetch settleable PN (ACTIVE, DEFAULT, or BAD_DEBT with balance remaining)
        $settleablePN = PromissoryNote::where('student_id', $student->id)
            ->whereIn('status', [
                PromissoryNote::STATUS_ACTIVE,
                PromissoryNote::STATUS_DEFAULT,
                PromissoryNote::STATUS_BAD_DEBT,
            ])
            ->where('remaining_balance', '>', 0)
            ->with('fees:id,fee_name')
            ->orderByDesc('id')
            ->first();

        if (!$settleablePN) {
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
            'fees' => $settleablePN->fees->map(fn($fee) => [
                'id' => $fee->id,
                'name' => $fee->fee_name,
                'amount_deferred' => $fee->pivot->amount_deferred,
            ])->toArray(),
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
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required|array|min:1',
            'fee_ids.*' => 'exists:fees,id',
            'cash_received' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
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

        $enrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->firstOrFail();

        // Verify student belongs to this college
        if ($enrollment->college_id !== $collegeId) {
            return response()->json(['message' => 'Student does not belong to this college.'], 403);
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

        // Admin collects only college-level fees (no org fees)
        $invalidFees = $fees->filter(function ($fee) use ($collegeId) {
            return $fee->fee_scope !== 'college'
                || $fee->college_id !== $collegeId
                || !is_null($fee->organization_id);
        })->pluck('id')->toArray();

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
        $collegeCode = ($college?->college_code ?? 'UNK') . '-TRES';
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
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
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

        $enrollment->refreshFinancialStatus();

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

    /**
     * Collect promissory note settlement payment
     */
    private function collectPromissoryPayment(CollectPromissoryPaymentRequest $request)
    {

        try {
            $user = Auth::user();
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
            if ($promissoryNote->enrollment->college_id !== $collegeId) {
                return response()->json([
                    'message' => 'Promissory note does not belong to this college.'
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
            return response()->json([
                'message' => $e->getMessage(),
                'error_type' => class_basename($e),
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