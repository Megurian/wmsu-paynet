<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Remittance;
use App\Models\Organization;

class CollegeOrgRemittanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $organization = $user->organization;


        abort_unless($organization, 404);

        $osaId = Organization::where(function ($q) {
            $q->where('org_code', 'OSA')
                ->orWhere('name', 'Office of Student Affairs')
                ->orWhere('role', 'osa');
        })->value('id');

        $query = Remittance::with(['fee', 'toOrganization'])
            ->where('from_organization_id', $organization->id);

        if ($request->filled('to_filter')) {

            if ($request->to_filter === 'osa') {
                $query->where('to_organization_id', $osaId);
            }

            if ($request->to_filter === 'mother') {
                $query->where('to_organization_id', $organization->mother_organization_id);
            }
        }

        $remittances = $query->latest()->get()->map(function ($remit) use ($osaId, $organization) {

            if ($remit->to_organization_id == $osaId) {
                $remit->type = 'OSA Inherited Fee';
            } elseif ($remit->to_organization_id == $organization->mother_organization_id) {
                $remit->type = 'Mother Organization Fee';
            } else {
                $remit->type = 'Other';
            }

            return $remit;
        });

        $motherOrg = $organization->motherOrganization; 
        $motherOrgId = $organization->mother_organization_id;

        $osaOrg = Organization::where(function ($q) {
            $q->where('org_code', 'OSA')
            ->orWhere('name', 'Office of Student Affairs')
            ->orWhere('role', 'osa');
        })->first();

        return view('college_org.remittance', compact(
            'remittances',
            'osaOrg',
            'motherOrg'
        ));
    }
}
