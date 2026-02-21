<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Fee;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\College;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class TreasurerCashieringController extends Controller
{
    public function index() {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $fees = Fee::where('status', 'APPROVED')
            ->where('fee_scope', 'college')
            ->where('college_id', auth()->user()->college_id)
            ->whereNull('organization_id')
            ->get();

        return view('college.cashiering', compact('fees'));
    }

    public function searchAdvisedStudents(Request $request)
    {
        $query = $request->query('q');
        $collegeId = auth()->user()->college_id;
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
        $collegeId = auth()->user()->college_id;
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

    public function collectPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required|array|min:1',
            'fee_ids.*' => 'exists:fees,id',
            'cash_received' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
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
            'amount_due' => $totalAmount,
            'cash_received' => $request->cash_received,
            'change' => $change,
            'collected_by' => auth()->id(),
            'transaction_id' => $transactionId,
        ]);

        foreach ($fees as $fee) {
            $payment->fees()->attach($fee->id, ['amount_paid' => $fee->amount]);
        }

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