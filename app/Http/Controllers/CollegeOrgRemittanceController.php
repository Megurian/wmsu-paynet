<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Remittance;
use App\Models\Organization;

class CollegeOrgRemittanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $organization = $user->organization;

        abort_unless($organization, 404);

        // Get OSA organization dynamically (NO hardcoding)
        $osaId = Organization::where('role', 'osa')->value('id');

        // Get ALL remittances from this org
        $remittances = Remittance::with(['fee', 'toOrganization'])
            ->where('from_organization_id', $organization->id)
            ->latest()
            ->get()
            ->map(function ($remit) use ($osaId, $organization) {

                // Tag type for display
                if ($remit->to_organization_id == $osaId) {
                    $remit->type = 'OSA Inherited Fee';
                } elseif ($remit->to_organization_id == $organization->mother_organization_id) {
                    $remit->type = 'Mother Organization Fee';
                } else {
                    $remit->type = 'Other';
                }

                return $remit;
            });

        return view('college_org.remittance', compact('remittances'));
    }
}