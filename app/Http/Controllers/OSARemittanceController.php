<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Payment;
use App\Models\Remittance;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OSARemittanceController extends Controller
{

    public function index()
    {

        $activeSY = SchoolYear::where('is_active', 1)->first();
        $activeSem = Semester::where('is_active', 1)->first();

        $motherOrgs = Organization::where('role', 'mother_org')
            ->with('childOrganizations')
            ->get();

        $expectedRemittance = 0;

        foreach ($motherOrgs as $org) {

            $childOrgIds = $org->childOrganizations->pluck('id');

            $collected = Payment::whereIn('organization_id', $childOrgIds)
                ->where('school_year_id', $activeSY?->id)
                ->where('semester_id', $activeSem?->id)
                ->sum('amount');

            $org->totalCollected = $collected;

            $remitted = Remittance::where('to_organization_id', $org->id)
                ->sum('amount');

            $org->totalRemitted = $remitted;

            $org->remaining = $collected - $remitted;

            $expectedRemittance += $collected;
        }

        $totalRemitted = Remittance::sum('amount');

        $pendingOrgs = $motherOrgs->where('remaining', '>', 0)->count();

        $records = Remittance::with([
            'fromOrganization',
            'toOrganization',
            'confirmer',
            'schoolYear',
            'semester'
        ])
        ->latest()
        ->paginate(10);

        return view('osa.remittance.index', compact(
            'motherOrgs',
            'records',
            'expectedRemittance',
            'totalRemitted',
            'pendingOrgs'
        ));
    }

}