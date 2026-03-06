<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Fee;
use Illuminate\Support\Facades\Auth;

class UniversityOrgReportsController extends Controller
{
    public function paymentCollectionReport(Request $request)
    {
        $user = Auth::user();
        $motherOrg = $user?->organization;

        if ($motherOrg && $motherOrg->role === 'university_org') {
            // Load child organizations with admin, college, and fees
            $childOrgs = $motherOrg->childOrganizations()
                ->with(['orgAdmin', 'college'])
                ->get();

            // manually load fees including university-wide fees
            $childOrgs->each(function ($org) {
                $org->setRelation('fees', Fee::where(function ($q) use ($org) {
                    $q->where('organization_id', $org->id)
                        ->orWhere('fee_scope', 'university-wide');
                })->orderBy('created_at', 'desc')->get());
            });
        } else {
            $childOrgs = collect(); // empty collection if not a university org
        }

        return view('university_org.reports', compact('motherOrg', 'childOrgs'));
    }
}
