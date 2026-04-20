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
    public function index()
    {
        $motherOrg = Auth::user();
        abort_unless($motherOrg, 403);
        $motherOrg = $motherOrg->organization;
        abort_unless($motherOrg, 404);

        $activeSY = SchoolYear::where('is_active', 1)->first();
        $activeSem = $activeSY?->semesters()->where('is_active', 1)->first();

        $childOrgs = $motherOrg->childOrganizations;

        $remittanceData = [];

        foreach ($childOrgs as $child) {
            $fees = Fee::where('organization_id', $motherOrg->id)->get();

            $totalCollectedPerChild = 0;
            $expectedPerChild = 0;
            $feeDetails = [];

            foreach ($fees as $fee) {
                $totalCollected = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('payments.organization_id', $child->id)
                    ->where('payments.school_year_id', $activeSY?->id)
                    ->where('payments.semester_id', $activeSem?->id)
                    ->where('fee_payment.fee_id', $fee->id)
                    ->sum('fee_payment.amount_paid');

                $studentsPaid = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('payments.organization_id', $child->id)
                    ->where('payments.school_year_id', $activeSY?->id)
                    ->where('payments.semester_id', $activeSem?->id)
                    ->where('fee_payment.fee_id', $fee->id)
                    ->count();

                $feeDetails[] = [
                    'fee' => $fee,
                    'studentsPaid' => $studentsPaid,
                    'totalCollected' => $totalCollected,
                ];

                $totalCollectedPerChild += $totalCollected;
                $expectedPerChild += $totalCollected * ($fee->remittance_percent / 100);
            }

            $remitted = Remittance::where('from_organization_id', $child->id)->sum('amount');

            $remaining = max(0, $expectedPerChild - $remitted);

            $status = 'Pending';
            if ($remaining <= 0 && $remitted > 0) {
                $status = 'Completed';
            } elseif ($remitted > 0) {
                $status = 'Partial';
            }

            // Pick the fee with the highest current expected remittance (based on collected amount)
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
            ->latest()
            ->get();

        return view('university_org.remittance', compact(
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
        ]);

        $motherOrg = $user->organization;
        abort_unless($motherOrg, 404);

        $childOrg = Organization::findOrFail($request->from_organization_id);
        if ($childOrg->mother_organization_id !== $motherOrg->id) {
            return back()->with('error', 'Invalid child organization for this mother organization.');
        }

        $fees = Fee::where('organization_id', $motherOrg->id)->get();

        if (! $fees->contains('id', $request->fee_id)) {
            return back()->with('error', 'Selected fee is not valid for this mother organization.');
        }

        $expected = 0;

        foreach ($fees as $fee) {
            $totalCollectedForFee = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('payments.organization_id', $childOrg->id)
                ->where('fee_payment.fee_id', $fee->id)
                ->sum('fee_payment.amount_paid');

            $expected += $totalCollectedForFee * ($fee->remittance_percent / 100);
        }

        $totalRemitted = Remittance::where('from_organization_id', $childOrg->id)->sum('amount');
        $remaining = max(0, $expected - $totalRemitted);

        if ($request->amount > $remaining) {
            return back()->with('error', 'Amount cannot exceed remaining balance of ₱' . number_format($remaining, 2));
        }

        $activeSY = SchoolYear::where('is_active', 1)->first();
        $activeSem = $activeSY?->semesters()->where('is_active', 1)->first();

        Remittance::create([
            'from_organization_id' => $childOrg->id,
            'to_organization_id' => $motherOrg->id,
            'fee_id' => $request->fee_id,
            'school_year_id' => $activeSY?->id,
            'semester_id' => $activeSem?->id,
            'amount' => $request->amount,
            'confirmed_by' => Auth::id(),
            'status' => 'confirmed'
        ]);

        return back()->with('status', 'Remittance confirmed successfully');
    }
}
