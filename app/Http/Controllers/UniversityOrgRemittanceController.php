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
        $motherOrg = Auth::user()->organization;

        $activeSY = SchoolYear::where('is_active', 1)->first();
        $activeSem = $activeSY?->semesters()->where('is_active', 1)->first();

        $childOrgs = $motherOrg->childOrganizations;

        $remittanceData = [];

        foreach ($childOrgs as $child) {
            $fees = Fee::where('organization_id', $motherOrg->id)->get();

            $totalCollectedPerChild = 0;
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
            }

            $remitted = Remittance::where('from_organization_id', $child->id)->sum('amount');

            $remaining = $totalCollectedPerChild - $remitted;

            $status = 'Pending';
            if ($remaining <= 0) {
                $status = 'Completed';
            } elseif ($remitted > 0) {
                $status = 'Partial';
            }

            $remittanceData[] = [
                'organization' => $child,
                'feeDetails' => $feeDetails,
                'totalCollected' => $totalCollectedPerChild,
                'remitted' => $remitted,
                'remaining' => $remaining,
                'status' => $status
            ];
        }

        $totalCollected = collect($remittanceData)->sum('totalCollected');
        $totalRemitted = collect($remittanceData)->sum('remitted');
        $remaining = $totalCollected - $totalRemitted;

        $totalExpected = collect($remittanceData)->sum(function ($row) {
            $sum = 0;
            foreach ($row['feeDetails'] as $fee) {
                $sum += $fee['totalCollected'] * ($fee['fee']->remittance_percent / 100);
            }
            return $sum;
        });

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
        $request->validate([
            'from_organization_id' => 'required|exists:organizations,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $motherOrg = Auth::user()->organization;
        $childOrg = Organization::findOrFail($request->from_organization_id);

        $fees = Fee::where('organization_id', $motherOrg->id)->get();
        $totalCollectedPerChild = 0;

        foreach ($fees as $fee) {
            $totalCollected = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('payments.organization_id', $childOrg->id)
                ->sum('fee_payment.amount_paid');

            $totalCollectedPerChild += $totalCollected;
        }

        $totalRemitted = Remittance::where('from_organization_id', $childOrg->id)->sum('amount');
        $remaining = $totalCollectedPerChild - $totalRemitted;

        if ($request->amount > $remaining) {
            return back()->with('error', 'Amount cannot exceed remaining balance of ₱' . number_format($remaining, 2));
        }

        $activeSY = SchoolYear::where('is_active', 1)->first();
        $activeSem = $activeSY?->semesters()->where('is_active', 1)->first();

        Remittance::create([
            'from_organization_id' => $childOrg->id,
            'to_organization_id' => $motherOrg->id,
            'school_year_id' => $activeSY?->id,
            'semester_id' => $activeSem?->id,
            'amount' => $request->amount,
            'confirmed_by' => Auth::id(),
            'status' => 'confirmed'
        ]);

        return back()->with('status', 'Remittance confirmed successfully');
    }
}
