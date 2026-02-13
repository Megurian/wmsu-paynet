<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Fee;

use App\Models\StudentEnrollment;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class OrganizationPaymentController extends Controller
{
    public function searchStudents(Request $request)
    {
        $query = $request->q;
        if (!$query) {
            return response()->json([]);
        }

        $user = Auth::user();
        $collegeId = $user->organization->college_id ?? null;
        if (!$collegeId) {
            return response()->json([]);
        }

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $students = Student::where('college_id', $collegeId)
            ->whereHas('enrollments', function ($q) use ($activeSY, $activeSem) {
                $q->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED'])
                ->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id);
            })
            ->where(function ($q) use ($query) {
                $q->where('student_id', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $enrollment = $s->enrollments->first();
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
    $student = Student::with(['enrollments' => function($q) {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();
        $q->where('school_year_id', $activeSY->id)
          ->where('semester_id', $activeSem->id)
          ->whereIn('status',['FOR_PAYMENT_VALIDATION','ENROLLED']);
    }, 'enrollments.course', 'enrollments.yearLevel', 'enrollments.section'])->findOrFail($studentId);

    $activeEnrollment = $student->enrollments->first();

    // Use the logged-in user's organization to fetch fees
    $userOrgId = auth()->user()->organization_id;

    $fees = Fee::where('organization_id', $userOrgId)
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json([
        'student' => [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'course' => $activeEnrollment?->course?->name,
            'year' => $activeEnrollment?->yearLevel?->name,
            'section' => $activeEnrollment?->section?->name,
        ],
        'fees' => $fees
    ]);
}


public function collectPayment(Request $request)
{
    $request->validate([
        'student_id'=>'required|exists:students,id',
        'fee_ids'=>'required|array',
        'fee_ids.*'=>'exists:fees,id',
        'cash_received'=>'required|numeric|min:0',
    ]);

    $student = Student::findOrFail($request->student_id);

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $enrollment = StudentEnrollment::where('student_id',$student->id)
        ->where('school_year_id',$activeSY->id)
        ->where('semester_id',$activeSem->id)
        ->firstOrFail();

    $fees = Fee::whereIn('id',$request->fee_ids)->get();
    $totalAmount = $fees->sum('amount');

    if($request->cash_received < $totalAmount){
        return response()->json(['message'=>'Cash received is less than total.'],422);
    }

    $change = $request->cash_received - $totalAmount;

    $alreadyPaid = DB::table('fee_payment')
        ->join('payments','fee_payment.payment_id','=','payments.id')
        ->where('payments.enrollment_id',$enrollment->id)
        ->pluck('fee_payment.fee_id')
        ->toArray();

    $duplicate = array_intersect($request->fee_ids, $alreadyPaid);
    if(!empty($duplicate)){
        return response()->json(['message'=>'Some fees were already paid.'],422);
    }

    $lastId = Payment::max('id') + 1;
    $transactionId = 'TRX'.now()->format('Ymd').str_pad($lastId,5,'0',STR_PAD_LEFT);

    $payment = Payment::create([
        'student_id'=>$student->id,
        'enrollment_id'=>$enrollment->id,
        'amount'=>$totalAmount,
        'cash_received'=>$request->cash_received,
        'change'=>$change,
        'collected_by'=>auth()->id(),
        'transaction_id'=>$transactionId,
    ]);

    foreach($fees as $fee){
        $payment->fees()->attach($fee->id,['amount'=>$fee->amount]);
    }

    $enrollment->update(['is_paid'=>true]);

    return response()->json([
        'message'=>'Payment collected successfully.',
        'payment_id'=>$payment->id,
        'transaction_id'=>$transactionId,
        'total'=>$totalAmount,
        'change'=>$change
    ]);
}
    
}
