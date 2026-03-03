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

        // Get child organizations
        $childOrgs = $motherOrg->childOrganizations()->with('college')->get();

        $activeSYId = SchoolYear::where('is_active', true)->value('id');
        $activeSemId = Semester::where('is_active', true)->value('id');

        $schoolYearId = $request->input('school_year_id', $activeSYId);
        $semesterId = $request->input('semester_id', $activeSemId);

        // Fetch payments for child organizations
        $payments = Payment::with(['student', 'fees', 'organization'])
            ->whereIn('organization_id', $childOrgs->pluck('id'))
            ->where('school_year_id', $schoolYearId)
            ->where('semester_id', $semesterId)
            ->get();

        // Group payments by organization
        $orgPayments = $childOrgs->map(function ($org) use ($payments) {
            $orgPayments = $payments->where('organization_id', $org->id);

            $totalCollected = $orgPayments->sum('amount_due');

            return [
                'organization' => $org,
                'payments' => $orgPayments,
                'total_collected' => $totalCollected,
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