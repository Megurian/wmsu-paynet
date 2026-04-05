<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\College;
use App\Models\Organization;
use App\Models\StudentEnrollment;
use App\Models\Payment;
use App\Models\Fee;

class OSADashController extends Controller
{
    public function index(Request $request)
    {
        $activeSY = SchoolYear::where('is_active', 1)->first();
        $activeSem = $activeSY ? $activeSY->semesters()->where('is_active', 1)->first() : null;

        $totalMotherOrgs = Organization::whereNull('college_id')->whereNull('mother_organization_id')->count();
        $totalChildOrgs = Organization::whereNotNull('mother_organization_id')->count();
        $totalLocalOrgs = Organization::whereNotNull('college_id')->whereNull('mother_organization_id')->count();
        $totalFees = Fee::count();
        $totalStudents = StudentEnrollment::where('school_year_id', $activeSY->id ?? null)
            ->where('semester_id', $activeSem->id ?? null)
            ->where('status', StudentEnrollment::ENROLLED)
            ->selectRaw('COUNT(DISTINCT student_id) as total')
            ->first()
            ->total ?? 0;

        $totalPaymentsCollected = Payment::where('school_year_id', $activeSY->id ?? null)
            ->where('semester_id', $activeSem->id ?? null)
            ->sum('amount_due');

        $studentsPaid = Payment::where('school_year_id', $activeSY->id ?? null)
            ->where('semester_id', $activeSem->id ?? null)
            ->distinct('student_id')
            ->count();

        $studentsPending = StudentEnrollment::where('school_year_id', $activeSY->id ?? null)
            ->where('semester_id', $activeSem->id ?? null)
            ->where('status', StudentEnrollment::FOR_PAYMENT_VALIDATION)
            ->selectRaw('COUNT(DISTINCT student_id) as total')
            ->first()
            ->total ?? 0;

        $paymentTrend = Payment::where('school_year_id', $activeSY->id ?? null)
            ->where('semester_id', $activeSem->id ?? null)
            ->selectRaw('DATE(created_at) as date, SUM(amount_due) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $organizations = Organization::with('college', 'childOrganizations')
            ->get()
            ->map(function ($org) use ($activeSY, $activeSem) {
                $org->totalPayments = Payment::where('organization_id', $org->id)
                    ->where('school_year_id', $activeSY->id ?? null)
                    ->where('semester_id', $activeSem->id ?? null)
                    ->sum('amount_due');

                $org->totalStudents = StudentEnrollment::where('college_id', $org->college_id)
                    ->where('school_year_id', $activeSY->id ?? null)
                    ->where('semester_id', $activeSem->id ?? null)
                    ->where('status', StudentEnrollment::ENROLLED)
                    ->selectRaw('COUNT(DISTINCT student_id) as total')
                    ->first()
                    ->total ?? 0;

                $org->studentsPaid = Payment::where('organization_id', $org->id)
                    ->where('school_year_id', $activeSY->id ?? null)
                    ->where('semester_id', $activeSem->id ?? null)
                    ->distinct('student_id')
                    ->count();

                $org->completionRate = $org->totalStudents > 0
                    ? round(($org->studentsPaid / $org->totalStudents) * 100, 2)
                    : 0;

                return $org;
            });

        $fees = Fee::with('organization')->get()
            ->map(function ($fee) use ($activeSY, $activeSem) {
                $fee->totalPaid = Payment::where('school_year_id', $activeSY->id ?? null)
                    ->where('semester_id', $activeSem->id ?? null)
                    ->whereHas('fees', function ($q) use ($fee) {
                        $q->where('fee_id', $fee->id);
                    })
                    ->sum('amount_due');

                $fee->totalPaidCount = Payment::where('school_year_id', $activeSY->id ?? null)
                    ->where('semester_id', $activeSem->id ?? null)
                    ->whereHas('fees', function ($q) use ($fee) {
                        $q->where('fee_id', $fee->id);
                    })
                    ->distinct('student_id')
                    ->count();

                $fee->totalStudents = StudentEnrollment::where('school_year_id', $activeSY->id ?? null)
                    ->where('semester_id', $activeSem->id ?? null)
                    ->where('status', StudentEnrollment::ENROLLED)
                    ->selectRaw('COUNT(DISTINCT student_id) as total')
                    ->first()
                    ->total ?? 0;

                $fee->progress = $fee->totalStudents > 0
                    ? round(($fee->totalPaidCount / $fee->totalStudents) * 100, 2)
                    : 0;

                return $fee;
            });

        $recentPayments = Payment::with([ 'student', 'fees.organization.college' ]) ->latest() ->take(10) ->get();

        return view('osa.dashboard', compact(
            'activeSY',
            'activeSem',
            'totalMotherOrgs',
            'totalChildOrgs',
            'totalLocalOrgs',
            'totalFees',
            'totalStudents',
            'studentsPaid',
            'studentsPending',
            'totalPaymentsCollected',
            'paymentTrend',
            'organizations',
            'fees',
            'recentPayments'
        ));
    }
}
