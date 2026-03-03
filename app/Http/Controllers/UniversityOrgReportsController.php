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

    $payments = Payment::with(['student', 'fees', 'organization'])
        ->whereIn('organization_id', $childOrgs->pluck('id'))
        ->where('school_year_id', $schoolYearId)
        ->where('semester_id', $semesterId)
        ->get();

    $orgPayments = $childOrgs->map(function ($org) use ($payments) {
        $orgPayments = $payments->where('organization_id', $org->id);

        $totalCollected = $orgPayments->sum('amount_due');

        $paidCount = $orgPayments->filter(function ($p) {
            return $p->fees->sum(fn($f) => $f->pivot->amount_paid ?? 0) >= $p->fees->sum('amount');
        })->count();

        $totalStudents = $orgPayments->pluck('student')->unique('id')->count();
        $pendingCount = $totalStudents - $paidCount;
        $paidPercentage = $totalStudents > 0 ? round(($paidCount / $totalStudents) * 100, 0) : 0;

        return [
            'organization' => $org,
            'payments' => $orgPayments,
            'total_collected' => $totalCollected,
            'total_students' => $totalStudents,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'paid_percentage' => $paidPercentage,
        ];
    });

    return view('university_org.reports', [
        'motherOrg' => $motherOrg,
        'orgPayments' => $orgPayments,
        'schoolYearId' => $schoolYearId,
        'semesterId' => $semesterId,
    ]);
}
}