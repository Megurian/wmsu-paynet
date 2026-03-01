<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CollegeFeeApprovalController extends Controller
{
    public function index(Request $request)
    {
        $collegeId = auth()->user()->college_id;
        $tab = $request->get('tab', 'pending');

        $baseQuery = Fee::with(['organization.motherOrganization'])
            ->where('fee_scope', 'college')
            ->where('college_id', $collegeId);

        // Include fees that require dean approval — this includes fees created by child organizations (they still need dean approval first).
        $pendingFees = (clone $baseQuery)
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->where('approval_level', 'dean')
                  ->orWhereNull('organization_id'); 
            })
            ->orderByDesc('created_at')
            ->get();

        // Base approved college fees
        $allFees = (clone $baseQuery)
            ->where('status', 'approved')
            ->orderByDesc('approved_at')
            ->get();

        // Include fees inherited by child organizations under this college
        $childOrgs = \App\Models\Organization::where('college_id', $collegeId)
            ->whereNotNull('mother_organization_id')
            ->with('motherOrganization')
            ->get();

        $motherOrgIds = $childOrgs->pluck('mother_organization_id')->unique()->filter()->values()->all();

        $inheritedFees = collect();

        if (!empty($motherOrgIds)) {
            $inheritedFees = \App\Models\Fee::with(['organization.motherOrganization'])
                ->where('status', 'approved')
                ->whereIn('organization_id', $motherOrgIds)
                ->get();

            // Special-case: include OSA fees if any child's mother org inherits OSA fees
            $hasOsaInheritingChild = $childOrgs->firstWhere('motherOrganization.inherits_osa_fees', true);
            if ($hasOsaInheritingChild) {
                $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
                if ($osaId) {
                    $osaFees = \App\Models\Fee::with(['organization.motherOrganization'])
                        ->where('status', 'approved')
                        ->where('organization_id', $osaId)
                        ->get();

                    $inheritedFees = $inheritedFees->merge($osaFees);
                }
            }
        }

        // Merge, dedupe and order by approved_at (desc)
        $allFees = $allFees->merge($inheritedFees)->unique('id')->sortByDesc('approved_at')->values();

        return view('college.fees.approval', compact('pendingFees', 'allFees', 'tab'));
    }

    /**
     * Show fee details to the College Dean (and allow approve/reject from details).
     */
    public function show(Fee $fee)
    {
        abort_unless($fee->college_id === auth()->user()->college_id, 403);

        $fee->load(['organization', 'appeals', 'user']);

        return view('college.fees.show', compact('fee'));
    }

    public function approve(Request $request, Fee $fee)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, auth()->user()->password)) {
            return back()->withErrors(['password' => 'Password verification failed.'])->withInput();
        }

        abort_unless($fee->college_id === auth()->user()->college_id, 403);

        // If this fee belongs to a college organization (college_org) or a child office,
        // the dean's approval should forward it to OSA for final approval (dean -> OSA).
        if ($fee->organization && (
                $fee->organization->role === 'college_org' ||
                $fee->organization->mother_organization_id
            )) {
            $fee->approval_level = 'osa';
            $fee->save();

            return back()->with('success', 'Dean approved — forwarded to OSA for final approval.');
        }

        $fee->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Fee approved.');
    }

    public function reject(Request $request, Fee $fee)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, auth()->user()->password)) {
            return back()->withErrors(['password' => 'Password verification failed.'])->withInput();
        }

        abort_unless($fee->college_id === auth()->user()->college_id, 403);

        $fee->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Fee rejected.');
    }
}
