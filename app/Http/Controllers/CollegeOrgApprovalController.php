<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class CollegeOrgApprovalController extends Controller
{
    public function index()
    {
        $orgs = Organization::where('status', 'pending')->get();
        return view('college.local_organizations.approvals', compact('orgs'));
    }

    public function approve(Organization $organization)
    {
        $organization->update(['status' => 'approved']);
        return back()->with('success', 'Organization approved.');
    }

    public function reject(Organization $organization)
    {
        $organization->update(['status' => 'rejected']);
        return back()->with('success', 'Organization rejected.');
    }
}