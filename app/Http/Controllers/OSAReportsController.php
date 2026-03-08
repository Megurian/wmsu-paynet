<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\College;
use App\Models\Organization;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Payment;
use App\Models\Fee;
use App\Models\StudentEnrollment;

class OSAReportsController extends Controller
{
    public function index(Request $request)
    {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = $activeSY ? $activeSY->semesters()->where('is_active', true)->first() : null;

        $selectedSYId = $request->input('school_year_id', $activeSY->id ?? null);
        $selectedSemId = $request->input('semester_id', $activeSem->id ?? null);

        $colleges = College::withCount('users')->get();

        $orgQuery = Organization::query()
            ->where(function ($q) use ($selectedSYId, $selectedSemId) {

                $q->where('created_school_year_id', '<', $selectedSYId)
                    ->orWhere(function ($q2) use ($selectedSYId, $selectedSemId) {

                        $q2->where('created_school_year_id', $selectedSYId)
                            ->where('created_semester_id', '<=', $selectedSemId);
                    });
            })
            ->selectRaw(
                'college_id, 
         SUM(CASE WHEN mother_organization_id IS NULL THEN 1 ELSE 0 END) as local_orgs_count,
         SUM(CASE WHEN mother_organization_id IS NOT NULL THEN 1 ELSE 0 END) as child_orgs_count'
            )
            ->groupBy('college_id')
            ->get()
            ->keyBy('college_id');

        foreach ($colleges as $college) {
            $college->local_orgs_count = $orgQuery[$college->id]->local_orgs_count ?? 0;
            $college->child_orgs_count = $orgQuery[$college->id]->child_orgs_count ?? 0;
        }

        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters = $selectedSYId ? Semester::where('school_year_id', $selectedSYId)->get() : collect();

        $motherOrgs = Organization::whereNull('college_id')
            ->whereNull('mother_organization_id')
            ->where(function ($q) use ($selectedSYId, $selectedSemId) {
                $q->where('created_school_year_id', '<', $selectedSYId)
                    ->orWhere(function ($q2) use ($selectedSYId, $selectedSemId) {
                        $q2->where('created_school_year_id', $selectedSYId)
                            ->where('created_semester_id', '<=', $selectedSemId);
                    });
            })
            ->get();

        $motherOrgs->loadCount('childOrganizations');

        $motherOrgs->map(function ($org) use ($selectedSYId, $selectedSemId) {
            $org->totalPayments = Payment::whereIn('organization_id', $org->childOrganizations->pluck('id'))
                ->where('school_year_id', $selectedSYId)
                ->where('semester_id', $selectedSemId)
                ->sum('amount_due');
            return $org;
        });

        $localOrgs = Organization::whereNotNull('college_id')
            ->whereNull('mother_organization_id')
            ->where('role', 'college_org')
            ->where(function ($q) use ($selectedSYId, $selectedSemId) {
                $q->where('created_school_year_id', '<', $selectedSYId)
                    ->orWhere(function ($q2) use ($selectedSYId, $selectedSemId) {
                        $q2->where('created_school_year_id', $selectedSYId)
                            ->where('created_semester_id', '<=', $selectedSemId);
                    });
            })
            ->with('college')
            ->get();

        $localOrgs->map(function ($org) use ($selectedSYId, $selectedSemId) {

            $org->totalPayments = Payment::where('organization_id', $org->id)
                ->where('school_year_id', $selectedSYId)
                ->where('semester_id', $selectedSemId)
                ->sum('amount_due');

            return $org;
        });

        return view('osa.reports', compact(
            'colleges',
            'schoolYears',
            'semesters',
            'selectedSYId',
            'selectedSemId',
            'activeSY',
            'activeSem',
            'motherOrgs',
            'localOrgs'
        ));
    }

    public function collegeDetails(Request $request, $collegeId)
    {
        $college = College::findOrFail($collegeId);

        $selectedSYId = $request->school_year_id;
        $selectedSemId = $request->semester_id;

        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters = $selectedSYId ? Semester::where('school_year_id', $selectedSYId)->get() : collect();

        $organizations = Organization::where('college_id', $collegeId)
            ->where(function ($q) use ($selectedSYId, $selectedSemId) {
                $q->where('created_school_year_id', '<', $selectedSYId)
                    ->orWhere(function ($q2) use ($selectedSYId, $selectedSemId) {
                        $q2->where('created_school_year_id', $selectedSYId)
                            ->where('created_semester_id', '<=', $selectedSemId);
                    });
            })
            ->get();

        $organizations->map(function ($org) use ($selectedSYId, $selectedSemId) {

            $org->totalPayments = Payment::where('organization_id', $org->id)
                ->where('school_year_id', $selectedSYId)
                ->where('semester_id', $selectedSemId)
                ->sum('amount_due');

            return $org;
        });

        $localOrgs = $organizations->whereNull('mother_organization_id');
        $childOrgs = $organizations->whereNotNull('mother_organization_id');

        return view('osa.reports.college-details', compact(
            'college',
            'localOrgs',
            'childOrgs',
            'schoolYears',
            'semesters',
            'selectedSYId',
            'selectedSemId'
        ));
    }

    public function organizationDetails(Request $request, $organizationId)
    {
        $org = Organization::findOrFail($organizationId);

        $selectedSYId = $request->school_year_id;
        $selectedSemId = $request->semester_id;

        $org->totalPayments = Payment::where('organization_id', $organizationId)
            ->where('school_year_id', $selectedSYId)
            ->where('semester_id', $selectedSemId)
            ->sum('amount_due');

        $childOrgs = $org->childOrganizations()
            ->with('college')
            ->get()
            ->map(function ($child) use ($selectedSYId, $selectedSemId) {
                $child->totalPayments = Payment::where('organization_id', $child->id)
                    ->where('school_year_id', $selectedSYId)
                    ->where('semester_id', $selectedSemId)
                    ->sum('amount_due');
                return $child;
            });

        if (is_null($org->college_id) && is_null($org->mother_organization_id)) {

            $students = StudentEnrollment::with('student')
                ->whereIn('college_id', $childOrgs->pluck('college_id'))
                ->where('school_year_id', $selectedSYId)
                ->where('semester_id', $selectedSemId)
                ->get();
        } else {
            $students = StudentEnrollment::with('student')
                ->where('college_id', $org->college_id)
                ->where('school_year_id', $selectedSYId)
                ->where('semester_id', $selectedSemId)
                ->get();
        }

        $fees = Fee::where(function ($q) use ($org, $selectedSYId, $selectedSemId) {
            $q->where('organization_id', $org->id)
                ->where(function ($q2) use ($selectedSYId, $selectedSemId) {
                    $q2->where('created_school_year_id', '<', $selectedSYId)
                        ->orWhere(function ($q3) use ($selectedSYId, $selectedSemId) {
                            $q3->where('created_school_year_id', $selectedSYId)
                                ->where('created_semester_id', '<=', $selectedSemId);
                        });
                });

            if ($org->mother_organization_id || $org->inherits_osa_fees) {
                $q->orWhere(function ($q4) use ($selectedSYId, $selectedSemId) {
                    $q4->where('fee_scope', 'university-wide')
                        ->where(function ($q5) use ($selectedSYId, $selectedSemId) {
                            $q5->where('created_school_year_id', '<', $selectedSYId)
                                ->orWhere(function ($q6) use ($selectedSYId, $selectedSemId) {
                                    $q6->where('created_school_year_id', $selectedSYId)
                                        ->where('created_semester_id', '<=', $selectedSemId);
                                });
                        });
                });
            }
        })
            ->withCount(['payments as payment_count' => function ($q) use ($selectedSYId, $selectedSemId) {
                $q->where('school_year_id', $selectedSYId)
                    ->where('semester_id', $selectedSemId);
            }])
            ->get();

        $fees->map(function ($fee) use ($students, $selectedSYId, $selectedSemId) {
            $fee->studentPayments = $students->map(function ($enrollment) use ($fee, $selectedSYId, $selectedSemId) {
                $payment = $enrollment->student->payments()
                    ->where('school_year_id', $selectedSYId)
                    ->where('semester_id', $selectedSemId)
                    ->whereHas('fees', function ($q) use ($fee) {
                        $q->where('fee_id', $fee->id);
                    })
                    ->first();

                return [
                    'student_name' => $enrollment->student->full_name,
                    'student_id' => $enrollment->student->id,
                    'status' => $payment ? 'Paid' : 'Pending',
                    'amount_paid' => $payment ? $payment->fees()->where('fee_id', $fee->id)->first()->pivot->amount_paid : 0
                ];
            });
            return $fee;
        });

        return view('osa.reports.organization-details', compact(
            'org',
            'childOrgs',
            'fees',
            'selectedSYId',
            'selectedSemId'
        ));
    }
}
