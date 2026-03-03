<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Payment;
use App\Models\Fee;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UniversityOrgReportsController extends Controller
{
  public function paymentCollectionReport(Request $request)
{
    $motherOrg = Auth::user()->organization;

    if (!$motherOrg) {
        abort(403, 'Organization not found.');
    }

    $childOrgs = $motherOrg->childOrganizations()->with('college', 'users')->get();

    $activeSYId = SchoolYear::where('is_active', true)->value('id');
    $activeSemId = Semester::where('is_active', true)->value('id');

    $schoolYearId = $request->input('school_year_id', $activeSYId);
    $semesterId = $request->input('semester_id', $activeSemId);

    $orgReports = $childOrgs->map(function ($org) use ($schoolYearId, $semesterId) {

    $enrollments = \App\Models\StudentEnrollment::with('student')
        ->where('college_id', $org->college_id)
        ->where('school_year_id', $schoolYearId)
        ->where('semester_id', $semesterId)
        ->get();

    $students = $enrollments->pluck('student')->unique('id');

    $fees = $org->fees;

    $totalFeeAmount = $fees->sum('amount');

    $payments = \App\Models\Payment::with('fees')
        ->where('organization_id', $org->id)
        ->where('school_year_id', $schoolYearId)
        ->where('semester_id', $semesterId)
        ->get();

    $studentReports = $students->map(function ($student) use ($payments, $totalFeeAmount) {

        $studentPayments = $payments->where('student_id', $student->id);

        $totalPaid = $studentPayments->flatMap(function ($payment) {
            return $payment->fees->map(function ($fee) {
                return $fee->pivot->amount_paid ?? 0;
            });
        })->sum();

        $status = $totalPaid >= $totalFeeAmount && $totalFeeAmount > 0
            ? 'PAID'
            : 'PENDING';

        return [
            'student' => $student,
            'total_paid' => $totalPaid,
            'status' => $status,
        ];
    });

    $paidCount = $studentReports->where('status', 'PAID')->count();
    $pendingCount = $studentReports->where('status', 'PENDING')->count();

    return [
        'organization' => $org,
        'students' => $studentReports,
        'total_students' => $studentReports->count(),
        'paid_count' => $paidCount,
        'pending_count' => $pendingCount,
        'paid_percentage' => $studentReports->count() > 0
            ? round(($paidCount / $studentReports->count()) * 100)
            : 0,
    ];
});


    return view('university_org.reports', [
        'motherOrg' => $motherOrg,
    'orgReports' => $orgReports,
    'schoolYearId' => $schoolYearId,
    'semesterId' => $semesterId,
    ]);
}
}