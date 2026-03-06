<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class UniversityOrgReportsController extends Controller
{
    public function paymentCollectionReport(Request $request)
    {
        $user = Auth::user();
        $motherOrg = $user?->organization;

        if ($motherOrg && $motherOrg->role === 'university_org') {
            // Load child organizations with admin and college info
            $childOrgs = $motherOrg->childOrganizations()->with(['orgAdmin', 'college'])->orderBy('name')->get();
        } else {
            $childOrgs = collect(); // empty collection if not a university org
        }

        return view('university_org.reports', compact('motherOrg', 'childOrgs'));
    }
}