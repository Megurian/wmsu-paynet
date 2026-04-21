<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Remittance;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;

class UniversityOrgRemittanceController extends Controller
{
    public function index(Request $request)
    {
        $motherOrg = Auth::user();
        abort_unless($motherOrg, 403);
        $motherOrg = $motherOrg->organization;
        abort_unless($motherOrg, 404);

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

        $childOrgs = $motherOrg->childOrganizations;
        $remittanceData = [];

        foreach ($childOrgs as $child) {
            $fees = Fee::where('organization_id', $motherOrg->id)->get();

            $totalCollectedPerChild = 0;
            $expectedPerChild = 0;
            $feeDetails = [];

            foreach ($fees as $fee) {
                $query = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('payments.organization_id', $child->id)
                    ->where('fee_payment.fee_id', $fee->id);

                if ($selectedSchoolYear) {
                    $query->where('payments.school_year_id', $selectedSchoolYear->id);
                }

                if ($selectedSemester) {
                    $query->where('payments.semester_id', $selectedSemester->id);
                }

                $totalCollected = $query->sum('fee_payment.amount_paid');

                $studentsPaid = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('payments.organization_id', $child->id)
                    ->where('fee_payment.fee_id', $fee->id);

                if ($selectedSchoolYear) {
                    $studentsPaid->where('payments.school_year_id', $selectedSchoolYear->id);
                }

                if ($selectedSemester) {
                    $studentsPaid->where('payments.semester_id', $selectedSemester->id);
                }

                $studentsPaid = $studentsPaid->count();

                $feeDetails[] = [
                    'fee' => $fee,
                    'studentsPaid' => $studentsPaid,
                    'totalCollected' => $totalCollected,
                ];

                $totalCollectedPerChild += $totalCollected;
                $expectedPerChild += $totalCollected * ($fee->remittance_percent / 100);
            }

            $remitted = Remittance::where('from_organization_id', $child->id)
                ->when($selectedSchoolYear, fn($query) => $query->where('school_year_id', $selectedSchoolYear->id))
                ->when($selectedSemester, fn($query) => $query->where('semester_id', $selectedSemester->id))
                ->sum('amount');

            $remaining = max(0, $expectedPerChild - $remitted);

            $status = 'Pending';
            if ($remaining <= 0 && $remitted > 0) {
                $status = 'Completed';
            } elseif ($remitted > 0) {
                $status = 'Partial';
            }

            $defaultFee = collect($feeDetails)
                ->sortByDesc(fn($f) => $f['totalCollected'] * ($f['fee']->remittance_percent / 100))
                ->first();

            $defaultFeeId = $defaultFee ? $defaultFee['fee']->id : null;

            $remittanceData[] = [
                'organization' => $child,
                'feeDetails' => $feeDetails,
                'defaultFeeId' => $defaultFeeId,
                'totalCollected' => $totalCollectedPerChild,
                'expected' => $expectedPerChild,
                'remitted' => $remitted,
                'remaining' => $remaining,
                'status' => $status
            ];
        }

        $totalCollected = collect($remittanceData)->sum('totalCollected');
        $totalRemitted = collect($remittanceData)->sum('remitted');
        $totalExpected = collect($remittanceData)->sum('expected');
        $remaining = max(0, $totalExpected - $totalRemitted);

        $history = Remittance::with(['fromOrganization', 'confirmer'])
            ->where('to_organization_id', $motherOrg->id)
            ->when($selectedSchoolYear, fn($query) => $query->where('school_year_id', $selectedSchoolYear->id))
            ->when($selectedSemester, fn($query) => $query->where('semester_id', $selectedSemester->id))
            ->latest()
            ->get();

        return view('university_org.remittance', compact(
            'schoolYears',
            'selectedSchoolYear',
            'selectedSemester',
            'remittanceData',
            'totalCollected',
            'totalRemitted',
            'totalExpected',
            'remaining',
            'history'
        ));
    }

    public function confirm(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate([
            'from_organization_id' => 'required|exists:organizations,id',
            'fee_id' => 'required|exists:fees,id',
            'amount' => 'required|numeric|min:0.01',
            'school_year_id' => 'required|exists:school_years,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $motherOrg = $user->organization;
        abort_unless($motherOrg, 404);

        $childOrg = Organization::findOrFail($request->from_organization_id);
        if ($childOrg->mother_organization_id !== $motherOrg->id) {
            return back()->with('error', 'Invalid child organization for this mother organization.');
        }

        $schoolYear = SchoolYear::findOrFail($request->school_year_id);
        $semester = Semester::where('id', $request->semester_id)
            ->where('school_year_id', $schoolYear->id)
            ->firstOrFail();

        $fees = Fee::where('organization_id', $motherOrg->id)->get();

        if (! $fees->contains('id', $request->fee_id)) {
            return back()->with('error', 'Selected fee is not valid for this mother organization.');
        }

        $expected = 0;

        foreach ($fees as $fee) {
            $totalCollectedForFee = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('payments.organization_id', $childOrg->id)
                ->where('payments.school_year_id', $schoolYear->id)
                ->where('payments.semester_id', $semester->id)
                ->where('fee_payment.fee_id', $fee->id)
                ->sum('fee_payment.amount_paid');

            $expected += $totalCollectedForFee * ($fee->remittance_percent / 100);
        }

        $totalRemitted = Remittance::where('from_organization_id', $childOrg->id)
            ->where('school_year_id', $schoolYear->id)
            ->where('semester_id', $semester->id)
            ->sum('amount');

        $remaining = max(0, $expected - $totalRemitted);

        if ($request->amount > $remaining) {
            return back()->with('error', 'Amount cannot exceed remaining balance of ₱' . number_format($remaining, 2));
        }

        Remittance::create([
            'from_organization_id' => $childOrg->id,
            'to_organization_id' => $motherOrg->id,
            'fee_id' => $request->fee_id,
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
            'amount' => $request->amount,
            'confirmed_by' => Auth::id(),
            'status' => 'confirmed'
        ]);

        return back()->with('status', 'Remittance confirmed successfully');
    }
}
