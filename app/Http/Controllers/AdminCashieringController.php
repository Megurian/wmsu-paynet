<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Fee;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Payment;
use Mpdf\Mpdf;

class AdminCashieringController extends Controller
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
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $students = Student::whereHas('enrollments', function($q) use ($activeSY, $activeSem) {
            $q->where('status', 'FOR_PAYMENT_VALIDATION')
              ->where('school_year_id', $activeSY->id)
              ->where('semester_id', $activeSem->id);
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
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $student = Student::with(['enrollments' => function($q) use ($activeSY,$activeSem){
            $q->where('school_year_id',$activeSY->id)
              ->where('semester_id',$activeSem->id);
        }])->findOrFail($studentId);

        $enrollment = $student->enrollments->first();

        $fees = Fee::where('status', 'APPROVED')
            ->where('fee_scope', 'college')
            ->where('college_id', auth()->user()->college_id)
            ->whereNull('organization_id')
            ->get();

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
            'fees' => $fees
        ]);
    }

    public function collectPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required|array',
            'fee_ids.*' => 'exists:fees,id',
            'cash_received' => 'required|numeric|min:0',
        ]);

        $student = Student::findOrFail($request->student_id);
        $enrollment = StudentEnrollment::where('student_id', $student->id)
                        ->where('status','FOR_PAYMENT_VALIDATION')
                        ->firstOrFail();

        $fees = Fee::whereIn('id', $request->fee_ids)->get();
        $totalAmount = $fees->sum('amount');

        if ($request->cash_received < $totalAmount) {
            return response()->json(['message'=>'Cash received is less than total.'], 422);
        }

        $change = $request->cash_received - $totalAmount;
        $lastId = Payment::max('id') + 1;
        $transactionId = 'TRX' . now()->format('Ymd') . str_pad($lastId, 5, '0', STR_PAD_LEFT);

        $payment = Payment::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'amount' => $totalAmount,
            'cash_received' => $request->cash_received,
            'change' => $change,
            'collected_by' => auth()->id(),
            'transaction_id' => $transactionId,
        ]);

        foreach ($fees as $fee) {
            $payment->fees()->attach($fee->id, ['amount'=>$fee->amount]);
        }

        $enrollment->update([
            'is_paid' => true,
            'status' => 'PAID'
        ]);

        return response()->json([
            'message'=>'Payment collected successfully.',
            'total'=>$totalAmount,
            'change'=>$change,
            'payment_id' => $payment->id,
            'transaction_id' => $transactionId,
        ]);
    }

    public function downloadReceipt($paymentId)
    {
        $payment = Payment::with(['student', 'fees', 'collector'])->findOrFail($paymentId);

        $html = view('college.receipt-pdf', compact('payment'))->render();

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
        $mpdf->WriteHTML($html);

        return $mpdf->Output("receipt-{$payment->transaction_id}.pdf", 'I');
    }
}