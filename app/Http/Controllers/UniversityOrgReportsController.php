<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Fee;
use Illuminate\Support\Facades\Auth;

class UniversityOrgReportsController extends Controller
{
    public function paymentCollectionReport(Request $request)
    {
        $user = Auth::user();
        $motherOrg = $user?->organization;

        if ($motherOrg && $motherOrg->role === 'university_org') {
            $childOrgs = $motherOrg->childOrganizations()
                ->with(['orgAdmin', 'college'])
                ->get();

            $childOrgs->each(function ($org) {
                $fees = Fee::where(function ($q) use ($org) {
                    $q->where('organization_id', $org->id)
                        ->orWhere('fee_scope', 'university-wide');
                })->orderBy('created_at', 'desc')->get();

                $fees->each(function ($fee) use ($org) {
                    $activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
                    $activeSem = \App\Models\Semester::where('is_active', true)->first();

                    $students = \App\Models\Student::whereHas('enrollments', function ($q) use ($org, $activeSY, $activeSem) {
                        $q->where('college_id', $org->college_id)
                            ->where('school_year_id', $activeSY->id)
                            ->where('semester_id', $activeSem->id)
                            ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
                    })->get();

                    $paidStudentIds = \App\Models\Payment::where('organization_id', $org->id)
                        ->where('school_year_id', $activeSY->id)
                        ->where('semester_id', $activeSem->id)
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

        return view('university_org.reports', compact('motherOrg', 'childOrgs'));
    }
}
