<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UniversityOrgReportsController extends Controller
{
    public function paymentCollectionReport(Request $request)
    {
        $user = Auth::user();
        $motherOrg = $user?->organization;

        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters = Semester::orderBy('id')->get();

        $selectedSY = $request->input('school_year_id')
            ? SchoolYear::find($request->school_year_id)
            : SchoolYear::where('is_active', true)->first();

        $selectedSem = $request->input('semester_id')
            ? Semester::find($request->semester_id)
            : Semester::where('is_active', true)->first();

        if ($motherOrg && $motherOrg->role === 'university_org') {
            $childOrgs = $motherOrg->childOrganizations()
                ->with(['orgAdmin', 'college'])
                ->get();

            $recentOrgs = $motherOrg->childOrganizations()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($org) use ($selectedSY, $selectedSem) {

                    $org->total_students = Student::whereHas('enrollments', function ($q) use ($org, $selectedSY, $selectedSem) {
                        $q->where('college_id', $org->college_id)
                            ->where('school_year_id', $selectedSY->id)
                            ->where('semester_id', $selectedSem->id)
                            ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
                    })->count();

                    $org->total_payments_collected = Payment::where('organization_id', $org->id)
                        ->where('school_year_id', $selectedSY->id)
                        ->where('semester_id', $selectedSem->id)
                        ->sum('cash_received');

                    return $org;
                });


            $childOrgs->each(function ($org) use ($selectedSY, $selectedSem) {
                $fees = Fee::where(function ($q) use ($org) {
                    $q->where('organization_id', $org->id)
                        ->orWhere('fee_scope', 'university-wide');
                })->orderBy('created_at', 'desc')->get();

                $fees->each(function ($fee) use ($org, $selectedSY, $selectedSem) {
                    $students = Student::whereHas('enrollments', function ($q) use ($org, $selectedSY, $selectedSem) {
                        $q->where('college_id', $org->college_id)
                            ->where('school_year_id', $selectedSY->id)
                            ->where('semester_id', $selectedSem->id)
                            ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
                    })->get();

                    $paidStudentIds = Payment::where('organization_id', $org->id)
                        ->where('school_year_id', $selectedSY->id)
                        ->where('semester_id', $selectedSem->id)
                        ->whereHas('fees', function ($q) use ($fee) {
                            $q->where('fee_id', $fee->id);
                        })
                        ->pluck('student_id')
                        ->toArray();

                    $fee->paid_students = $students->whereIn('id', $paidStudentIds);
                    $fee->pending_students = $students->whereNotIn('id', $paidStudentIds);
                });

                $org->setRelation('fees', $fees);
            });
        } else {
            $childOrgs = collect();
        }

        $totalChildOrgs = $childOrgs->count();

        $totalActiveFees = Fee::whereIn('organization_id', $childOrgs->pluck('id'))
            ->where('status', 'approved')
            ->count();

        $totalStudentsEnrolled = Student::whereHas('enrollments', function ($q) use ($childOrgs, $selectedSY, $selectedSem) {
            $q->whereIn('college_id', $childOrgs->pluck('college_id'))
                ->where('school_year_id', $selectedSY->id)
                ->where('semester_id', $selectedSem->id)
                ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
        })->count();

        $totalPaymentsCollected = Payment::whereIn('organization_id', $childOrgs->pluck('id'))
            ->where('school_year_id', $selectedSY->id)
            ->where('semester_id', $selectedSem->id)
            ->sum('cash_received');

        $paymentsPerDay = Payment::whereIn('organization_id', $childOrgs->pluck('id'))
            ->where('school_year_id', $selectedSY->id)
            ->where('semester_id', $selectedSem->id)
            ->selectRaw('DATE(created_at) as date, SUM(cash_received) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyPaymentLabels = $paymentsPerDay->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
        $dailyPaymentData = $paymentsPerDay->pluck('total')->toArray();


        $totalPaidStudents = Student::whereHas('payments', function ($q) use ($childOrgs, $selectedSY, $selectedSem) {
            $q->whereIn('organization_id', $childOrgs->pluck('id'))
                ->where('school_year_id', $selectedSY->id)
                ->where('semester_id', $selectedSem->id);
        })->count();

        $totalPendingStudents = $totalStudentsEnrolled - $totalPaidStudents;
        return view('university_org.reports', compact(
            'motherOrg',
            'childOrgs',
            'recentOrgs',
            'schoolYears',
            'semesters',
            'selectedSY',
            'selectedSem',
            'totalChildOrgs',
            'totalActiveFees',
            'totalStudentsEnrolled',
            'totalPaymentsCollected',
            'totalPaidStudents',
            'totalPendingStudents',
            'dailyPaymentLabels',
            'dailyPaymentData'
        ));
    }

    public function childOrganizations()
    {
        $user = Auth::user();
        $motherOrg = $user?->organization;

        if (!$motherOrg || $motherOrg->role !== 'university_org') {
            abort(403);
        }

        $childOrgs = $motherOrg->childOrganizations()
            ->with(['orgAdmin', 'college'])
            ->orderBy('name')
            ->get();

        return view('university_org.child_organizations', compact(
            'motherOrg',
            'childOrgs'
        ));
    }
}
