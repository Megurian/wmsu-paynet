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

    public function index()
{
    $currentSY = SchoolYear::latest()->first();
    $currentSem = Semester::latest()->first();

    $osaOrg = Organization::where('org_code', 'OSA')->first();

    $childOrgs = Organization::where('role', 'college_org')
        ->whereNotNull('college_id')
        ->whereNotNull('mother_organization_id')
        ->whereHas('motherOrganization', function ($q) {
            $q->where('inherits_osa_fees', 1);
        })
        ->get();

    $remittanceData = [];
    $totalCollected = 0;    // Total OSA portion collected
    $totalExpected = 0;     // Total expected remittance
    $totalRemitted = 0;     // Already remitted amount

    // Get all approved OSA fees
    $fees = Fee::where('organization_id', $osaOrg->id)
        ->where('status', 'approved')
        ->get();

    foreach ($childOrgs as $org) {

        $feeDetails = [];
        $orgCollected = 0; // Sum of remittance amounts for this org

        foreach ($fees as $fee) {

            // Students who paid this fee
            $studentsPaid = DB::table('fee_payment')
                ->join('payments', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('fee_payment.fee_id', $fee->id)
                ->where('payments.organization_id', $org->id)
                ->count();

            // Total collected for this fee
            $totalFeeCollected = DB::table('fee_payment')
                ->join('payments', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('fee_payment.fee_id', $fee->id)
                ->where('payments.organization_id', $org->id)
                ->sum('fee_payment.amount_paid');

            // Calculate remittance portion based on remittance_percent (default 100%)
            $percent = $fee->remittance_percent ?? 100;
            $feeRemittanceAmount = ($totalFeeCollected * $percent) / 100;

            // Add to feeDetails for table
            $feeDetails[] = [
                'fee' => $fee,
                'studentsPaid' => $studentsPaid,
                'totalCollected' => $totalFeeCollected,    // total paid by students
                'remittanceAmount' => $feeRemittanceAmount // portion owed to OSA
            ];

            // Sum only the OSA portion
            $orgCollected += $feeRemittanceAmount;
        }

        // Already remitted amount for this org
        $remitted = Remittance::where('from_organization_id', $org->id)
            ->whereIn('fee_id', $fees->pluck('id'))
            ->sum('amount');

        $remaining = max($orgCollected - $remitted, 0);

        // Determine status
        $status = 'Pending';
        if ($remaining <= 0 && $orgCollected > 0) $status = 'Completed';
        elseif ($remitted > 0) $status = 'Partial';

        // Add to remittanceData
        $remittanceData[] = [
            'organization' => $org,
            'feeDetails' => $feeDetails,
            'collected' => $orgCollected, // total expected remittance for this org
            'expected' => $orgCollected,
            'remitted' => $remitted,
            'remaining' => $remaining,
            'status' => $status
        ];

        // Sum totals for dashboard
        $totalCollected += $orgCollected;
        $totalExpected += $orgCollected;
        $totalRemitted += $remitted;
    }

    $remaining = max($totalExpected - $totalRemitted, 0);

    // Get remittance history
    $osaFees = Fee::where('organization_id', $osaOrg->id)->pluck('id');

    $history = Remittance::whereIn('fee_id', $osaFees)
        ->whereHas('fromOrganization', function ($q) {
            $q->where('role', 'college_org');
        })
        ->with(['fromOrganization', 'confirmer'])
        ->latest()
        ->get();

    return view('osa.remittance', compact(
        'remittanceData',
        'totalCollected',
        'totalExpected',
        'totalRemitted',
        'remaining',
        'history'
    ));
}


    /*
    |--------------------------------------------------------------------------
    | Confirm Remittance
    |--------------------------------------------------------------------------
    */

    public function confirm(Remittance $remittance)
    {

        $remittance->update([
            'status' => 'confirmed',
            'confirmed_by' => Auth::id()
        ]);

        return back()->with('success', 'Remittance confirmed successfully');
    }
}
