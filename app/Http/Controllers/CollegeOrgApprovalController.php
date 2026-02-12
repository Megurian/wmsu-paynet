<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollegeOrgApprovalController extends Controller
{
    public function index()
    {
        $orgs = Organization::where('college_id', Auth::user()->college_id)
            ->whereNull('mother_organization_id') 
            ->where('status', 'pending')
            ->get();

        return view('college.local_organizations.approvals', compact('orgs'));
    }

    public function approve(Organization $organization)
    {
        if ($organization->status === 'pending' && is_null($organization->mother_organization_id)) {
            $organization->update(['status' => 'approved']);
            return back()->with('success', 'Organization approved.');
        }

        return back()->with('error', 'This organization cannot be approved.');
    }

    public function reject(Organization $organization)
    {
        if ($organization->status === 'pending' && is_null($organization->mother_organization_id)) {
            $organization->update(['status' => 'rejected']);
            return back()->with('success', 'Organization rejected.');
        }

        return back()->with('error', 'This organization cannot be rejected.');
    }
}