<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CollegeFeeApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $collegeId = $user->college_id;
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
        $pendingRequests = FeeRequest::with(['fee.organization', 'requestedBy'])
            ->where('status', 'pending')
            ->latest()
            ->get();
            
       $approvedFees = (clone $baseQuery)
            ->where('status', 'approved')
            ->whereDoesntHave('feeRequests', function ($q) {
                $q->where('status', 'pending')
                ->whereIn('type', ['disable', 'enable']);
            })
            ->orderByDesc('approved_at')
            ->get();
        $disabledFees = (clone $baseQuery)
        ->where('status', 'disabled')
        ->orderByDesc('disable_approved_at')
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
                ->whereDoesntHave('feeRequests', function ($q) {
                    $q->where('status', 'pending')
                    ->whereIn('type', ['disable', 'enable']);
                })
                ->whereIn('organization_id', $motherOrgIds)
                ->get();

            // Special-case: include OSA fees if any child's mother org inherits OSA fees
            $hasOsaInheritingChild = $childOrgs->firstWhere('motherOrganization.inherits_osa_fees', true);
            if ($hasOsaInheritingChild) {
                $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
                if ($osaId) {
                    $osaFees = \App\Models\Fee::with(['organization.motherOrganization'])
                        ->where('status', 'approved')
                        ->whereDoesntHave('feeRequests', function ($q) {
                            $q->where('status', 'pending')
                            ->whereIn('type', ['disable', 'enable']);
                        })
                        ->where('organization_id', $osaId)
                        ->get();

                    $inheritedFees = $inheritedFees->merge($osaFees);
                }
            }
        }

        // Merge, dedupe and order by approved_at (desc)
        $approvedFees = $approvedFees->merge($inheritedFees)->unique('id')->sortByDesc('approved_at')->values();

        return view('college.fees.approval', compact('pendingFees', 'approvedFees', 'pendingRequests','disabledFees', 'tab'));
    }

    /**
     * Show fee details to the College Dean (and allow approve/reject from details).
     */
    public function show(Fee $fee)
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless($fee->college_id === $user->college_id, 403);

        $fee->load(['organization', 'appeals', 'user']);

        return view('college.fees.show', compact('fee'));
    }

    public function approve(Request $request, Fee $fee)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password verification failed.'])->withInput();
        }

        abort_unless($fee->college_id === $user->college_id, 403);

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

        log_activity(
        'Fee Forwarded to OSA',
            "Fee '{$fee->fee_name}' forwarded to OSA for final approval",
            null, null, null,
            [
                'fee_id' => $fee->id,
                'college_id' => $fee->college_id,
                'from_level' => 'dean',
                'to_level' => 'osa',
                'action_by' => $user->id,
            ]
        );

        log_activity(
            'Fee Approved',
            "Fee '{$fee->fee_name}' approved by dean",
            null, null, null,
            [
                'fee_id' => $fee->id,
                'college_id' => $fee->college_id,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'final_status' => 'approved',
            ]
        );

        return back()->with('success', 'Fee approved.');
    }

    public function requestDisable(Request $request, Fee $fee)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        if ($fee->status === 'disabled') {
            return back()->with('error', 'Already disabled.');
        }

        FeeRequest::create([
            'fee_id' => $fee->id,
            'type' => 'disable',
            'status' => 'pending',
            'reason' => $request->reason,
            'requested_by' => $user->id,
            'requested_at' => now(),
        ]);

        log_activity(
            'Requested Fee Disable',
            "Disable request submitted for fee '{$fee->fee_name}'",
            null, null, null,
            [
                'fee_id' => $fee->id,
                'college_id' => $fee->college_id,
                'reason' => $request->reason,
                'requested_by' => $user->id,
                'status' => 'pending',
            ]
        );

        return back()->with('success', 'Disable request sent to OSA.');
    }

    public function requestEnable(Request $request, Fee $fee)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        if ($fee->status !== 'disabled') {
            return back()->with('error', 'Fee is not disabled.');
        }

        FeeRequest::create([
            'fee_id' => $fee->id,
            'type' => 'enable',
            'status' => 'pending',
            'reason' => $request->reason,
            'requested_by' => $user->id,
            'requested_at' => now(),
        ]);

        log_activity(
            'Requested Fee Enable',
            "Enable request submitted for fee '{$fee->fee_name}'",
            null, null, null,
            [
                'fee_id' => $fee->id,
                'college_id' => $fee->college_id,
                'reason' => $request->reason,
                'requested_by' => $user->id,
                'status' => 'pending',
            ]
        );

        return back()->with('success', 'Enable request sent to OSA.');
    }

  
}
