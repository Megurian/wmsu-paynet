<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Payment;
use App\Models\Remittance;
use App\Models\Fee;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OSARemittanceController extends Controller
{

    public function index(Request $request)
    {
        $schoolYears = SchoolYear::with('semesters')->orderByDesc('sy_start')->get();
        $activeSY = $schoolYears->firstWhere('is_active', true) ?? $schoolYears->first();

        $selectedSchoolYear = $schoolYears->firstWhere('id', $request->query('school_year_id')) ?? $activeSY;
        $selectedSemester = null;

        if ($request->filled('semester_id')) {
            $selectedSemester = $selectedSchoolYear?->semesters->firstWhere('id', $request->query('semester_id'));
        }

        $selectedSemester = $selectedSemester
            ?? $selectedSchoolYear?->semesters->firstWhere('is_active', true)
            ?? $selectedSchoolYear?->semesters->first();

        $osaOrg = Organization::firstOrCreate(
            ['org_code' => 'OSA'],
            ['name' => 'Office of Student Affairs', 'role' => 'university_org']
        );

        $childOrgs = Organization::where('role', 'college_org')
            ->whereNotNull('college_id')
            ->whereNotNull('mother_organization_id')
            ->whereHas('motherOrganization', function ($q) {
                $q->where('inherits_osa_fees', 1);
            })
            ->get();

        $remittanceData = [];
        $totalCollected = 0;
        $totalExpected = 0;
        $totalRemitted = 0;

        $fees = Fee::where('organization_id', $osaOrg->id)
            ->where('status', 'approved')
            ->get();

        foreach ($childOrgs as $org) {
            $feeDetails = [];
            $orgCollected = 0;

            foreach ($fees as $fee) {
                $studentsPaidQuery = DB::table('fee_payment')
                    ->join('payments', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('fee_payment.fee_id', $fee->id)
                    ->where('payments.organization_id', $org->id);

                $totalFeeCollectedQuery = DB::table('fee_payment')
                    ->join('payments', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('fee_payment.fee_id', $fee->id)
                    ->where('payments.organization_id', $org->id);

                if ($selectedSchoolYear) {
                    $studentsPaidQuery->where('payments.school_year_id', $selectedSchoolYear->id);
                    $totalFeeCollectedQuery->where('payments.school_year_id', $selectedSchoolYear->id);
                }

                if ($selectedSemester) {
                    $studentsPaidQuery->where('payments.semester_id', $selectedSemester->id);
                    $totalFeeCollectedQuery->where('payments.semester_id', $selectedSemester->id);
                }

                $studentsPaid = $studentsPaidQuery->count();
                $totalFeeCollected = $totalFeeCollectedQuery->sum('fee_payment.amount_paid');

                $percent = $fee->remittance_percent ?? 100;
                $feeRemittanceAmount = ($totalFeeCollected * $percent) / 100;

                $feeDetails[] = [
                    'fee' => $fee,
                    'studentsPaid' => $studentsPaid,
                    'totalCollected' => $totalFeeCollected,
                    'remittanceAmount' => $feeRemittanceAmount
                ];

                $orgCollected += $feeRemittanceAmount;
            }

            $remitted = Remittance::where('from_organization_id', $org->id)
                ->whereIn('fee_id', $fees->pluck('id'))
                ->when($selectedSchoolYear, fn($query) => $query->where('school_year_id', $selectedSchoolYear->id))
                ->when($selectedSemester, fn($query) => $query->where('semester_id', $selectedSemester->id))
                ->sum('amount');

            $remaining = max($orgCollected - $remitted, 0);

            $status = 'Pending';
            if ($remaining <= 0 && $orgCollected > 0) {
                $status = 'Completed';
            } elseif ($remitted > 0) {
                $status = 'Partial';
            }

            $remittanceData[] = [
                'organization' => $org,
                'feeDetails' => $feeDetails,
                'collected' => $orgCollected,
                'expected' => $orgCollected,
                'remitted' => $remitted,
                'remaining' => $remaining,
                'status' => $status
            ];

            $totalCollected += $orgCollected;
            $totalExpected += $orgCollected;
            $totalRemitted += $remitted;
        }

        $remaining = max($totalExpected - $totalRemitted, 0);
        $osaFees = Fee::where('organization_id', $osaOrg->id)->pluck('id');

        $history = Remittance::whereIn('fee_id', $osaFees)
            ->when($selectedSchoolYear, fn($query) => $query->where('school_year_id', $selectedSchoolYear->id))
            ->when($selectedSemester, fn($query) => $query->where('semester_id', $selectedSemester->id))
            ->whereHas('fromOrganization', function ($q) {
                $q->where('role', 'college_org');
            })
            ->with(['fromOrganization', 'confirmer'])
            ->latest()
            ->get();

        return view('osa.remittance', compact(
            'schoolYears',
            'selectedSchoolYear',
            'selectedSemester',
            'remittanceData',
            'totalCollected',
            'totalExpected',
            'totalRemitted',
            'remaining',
            'history'
        ));
    }


    public function confirm(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'amount' => 'required|numeric|min:0.01',
            'school_year_id' => 'required|exists:school_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $osaOrg = Organization::firstOrCreate(
            ['org_code' => 'OSA'],
            ['name' => 'Office of Student Affairs', 'role' => 'super_admin']
        );

        $schoolYear = SchoolYear::findOrFail($request->school_year_id);
        $semester = Semester::where('id', $request->semester_id)
            ->where('school_year_id', $schoolYear->id)
            ->firstOrFail();

        $fee = Fee::where('organization_id', $osaOrg->id)
            ->where('status', 'approved')
            ->first();

        if (! $fee) {
            return back()->withErrors(['fee' => 'No approved OSA fee is configured for remittance.']);
        }

        $fees = Fee::where('organization_id', $osaOrg->id)
            ->where('status', 'approved')
            ->get();

        $expected = 0;

        foreach ($fees as $feeItem) {
            $totalFeeCollected = DB::table('fee_payment')
                ->join('payments', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('fee_payment.fee_id', $feeItem->id)
                ->where('payments.organization_id', $request->organization_id)
                ->where('payments.school_year_id', $schoolYear->id)
                ->where('payments.semester_id', $semester->id)
                ->sum('fee_payment.amount_paid');

            $percent = $feeItem->remittance_percent ?? 100;
            $expected += ($totalFeeCollected * $percent) / 100;
        }

        $totalRemitted = Remittance::where('from_organization_id', $request->organization_id)
            ->where('school_year_id', $schoolYear->id)
            ->where('semester_id', $semester->id)
            ->sum('amount');

        $remaining = max($expected - $totalRemitted, 0);

        if ($request->amount > $remaining) {
            return back()->with('error', 'Amount cannot exceed remaining balance of ₱' . number_format($remaining, 2));
        }

        Remittance::create([
            'from_organization_id' => $request->organization_id,
            'to_organization_id' => $osaOrg->id,
            'fee_id' => $fee->id,
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
            'amount' => $request->amount,
            'status' => 'confirmed',
            'confirmed_by' => Auth::id()
        ]);

        return back()->with('status', 'Remittance confirmed successfully.');
    }
}
