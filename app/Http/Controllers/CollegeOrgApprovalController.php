<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollegeOrgApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $tab = $request->get('tab', 'pending');
        $collegeId = $user->college_id;

    $pendingCount = Organization::where('college_id', $collegeId)
        ->whereNull('mother_organization_id')
        ->where('status', 'pending')
        ->count();

    if ($tab === 'pending') {
        $orgs = Organization::where('college_id', $collegeId)
            ->whereNull('mother_organization_id')
            ->where('status', 'pending')
            ->get();
    } else {
        $studentOrgs = Organization::where('college_id', $collegeId)
            ->whereNull('mother_organization_id')
            ->where('status', 'approved')
            ->get();

        $motherOrgOffices = Organization::where('college_id', $collegeId)
            ->whereNotNull('mother_organization_id')
            ->with('motherOrganization')
            ->get();

        $orgs = $studentOrgs->concat($motherOrgOffices);
    }

    return view('college.local_organizations.approvals', compact('orgs', 'tab', 'pendingCount'));
}

    public function approve(Organization $organization)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($organization->college_id === $user->college_id && $organization->status === 'pending' && is_null($organization->mother_organization_id)) {
            $organization->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
            return back()->with('success', 'Organization approved.');
        }

        $organization->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        log_activity(
            'Approved Organization',
            "Approved organization '{$organization->name}'",
            null,
            [
                'organization_id' => $organization->id,
                'college_id' => $organization->college_id,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]
        );

        log_activity(
            'Organization Approval Failed',
            "Failed attempt to approve organization '{$organization->name}'",
            null,
            [
                'organization_id' => $organization->id,
                'college_id' => $organization->college_id,
                'attempted_by' => $user->id,
                'status' => $organization->status,
            ]
        );

        return back()->with('error', 'Only pending student-coordinator organizations can be approved.');
    }

    public function reject(Organization $organization)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($organization->college_id === $user->college_id && $organization->status === 'pending' && is_null($organization->mother_organization_id)) {
            $organization->update(['status' => 'rejected']);
            return back()->with('success', 'Organization rejected.');
        }

        $organization->update(['status' => 'rejected']);

        log_activity(
            'Rejected Organization',
            "Rejected organization '{$organization->name}'",
            null,
            [
                'organization_id' => $organization->id,
                'college_id' => $organization->college_id,
                'rejected_by' => $user->id,
            ]
        );

        log_activity(
            'Organization Rejection Failed',
            "Failed attempt to reject organization '{$organization->name}'",
            null,
            [
                'organization_id' => $organization->id,
                'college_id' => $organization->college_id,
                'attempted_by' => $user->id,
                'status' => $organization->status,
            ]
        );

        return back()->with('error', 'Only pending student-coordinator organizations can be rejected.');
    }
}