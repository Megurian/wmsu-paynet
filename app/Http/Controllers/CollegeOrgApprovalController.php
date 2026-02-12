<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollegeOrgApprovalController extends Controller
{
    public function index()
    {
        // Get all student-coordinator orgs (need approval)
        $studentOrgs = Organization::where('college_id', Auth::user()->college_id)
            ->whereNull('mother_organization_id')
            ->get();

        // Get all mother org offices (just for view)
        $motherOrgOffices = Organization::where('college_id', Auth::user()->college_id)
            ->whereNotNull('mother_organization_id')
            ->with('motherOrganization') // eager load mother org
            ->get();

        // Merge collections
        $orgs = $studentOrgs->concat($motherOrgOffices);

        return view('college.local_organizations.approvals', compact('orgs'));
    }

    public function approve(Organization $organization)
    {
        if ($organization->status === 'pending' && is_null($organization->mother_organization_id)) {
            $organization->update(['status' => 'approved']);
            return back()->with('success', 'Organization approved.');
        }

        return back()->with('error', 'Only pending student-coordinator organizations can be approved.');
    }

    public function reject(Organization $organization)
    {
        if ($organization->status === 'pending' && is_null($organization->mother_organization_id)) {
            $organization->update(['status' => 'rejected']);
            return back()->with('success', 'Organization rejected.');
        }

        return back()->with('error', 'Only pending student-coordinator organizations can be rejected.');
    }
}