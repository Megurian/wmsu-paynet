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


        $childOrgs = Organization::where('role', 'college_org')
            ->whereNotNull('college_id')
            ->whereNotNull('mother_organization_id')
            ->whereHas('motherOrganization', function ($q) {
                $q->where('inherits_osa_fees', 1);
            })
            ->get();

        $osaOrg = Organization::where('org_code', 'OSA')->first();
        $expectedRemittances = [];

        foreach ($childOrgs as $org) {

            $motherOrg = $org->motherOrganization;

            $fees = Fee::where('organization_id', $osaOrg->id)
                ->where('status', 'approved')
                ->get();

            foreach ($fees as $fee) {

                $totalCollected = DB::table('fee_payment')
                    ->join('payments', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('fee_payment.fee_id', $fee->id)
                    ->where('payments.organization_id', $org->id)
                    ->sum('fee_payment.amount_paid');

                $totalRemitted = Remittance::where('from_organization_id', $org->id)
                    ->where('fee_id', $fee->id)
                    ->sum('amount');

                $remaining = $totalCollected - $totalRemitted;

                $lastRemittance = Remittance::where('from_organization_id', $org->id)
                    ->where('fee_id', $fee->id)
                    ->latest()
                    ->first();

                $expectedRemittances[] = [
                    'organization' => $org,
                    'fee' => $fee,
                    'total_collected' => $totalCollected,
                    'total_remitted' => $totalRemitted,
                    'remaining' => $remaining,
                    'last_remittance' => $lastRemittance?->created_at,
                    'status' => $remaining <= 0 ? 'completed' : ($totalRemitted > 0 ? 'partial' : 'pending')
                ];
            }
        }
        /*
        |--------------------------------------------------------------------------
        | Dashboard Totals
        |--------------------------------------------------------------------------
        */

        $totalExpected = collect($expectedRemittances)->sum('total_collected');

        $totalRemitted = collect($expectedRemittances)->sum('total_remitted');

        $remainingTotal = $totalExpected - $totalRemitted;

        $pendingOrgs = collect($expectedRemittances)
            ->where('status', '!=', 'completed')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Remittance Records
        |--------------------------------------------------------------------------
        */

        $remittances = Remittance::whereHas('fromOrganization', function ($q) {
            $q->where('role', 'college_org');
        })
            ->with([
                'fromOrganization',
                'fee',
                'confirmer'
            ])
            ->latest()
            ->get();


        return view('osa.remittance', compact(
            'expectedRemittances',
            'totalExpected',
            'totalRemitted',
            'remainingTotal',
            'pendingOrgs',
            'remittances'
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
