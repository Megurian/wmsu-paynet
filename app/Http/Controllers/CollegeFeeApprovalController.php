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
        $search = $request->input('search');
        $organizationId = $request->input('organization_id');

        $organizations = \App\Models\Organization::query()
            ->where(function ($query) use ($collegeId) {
                $query->whereNull('college_id')
                      ->orWhere('college_id', $collegeId);
            })
            ->orderBy('name')
            ->get();

        $selectedOrg = $organizationId ? \App\Models\Organization::with('motherOrganization')->find($organizationId) : null;

        $collegeFeeScope = function ($query) use ($collegeId) {
            $query->where('fee_scope', 'college')
                  ->where(function ($query) use ($collegeId) {
                      $query->where('college_id', $collegeId)
                            ->orWhereHas('organization', function ($query) use ($collegeId) {
                                $query->where('college_id', $collegeId);
                            });
                  });
        };

        $baseQuery = Fee::with(['organization.motherOrganization'])
            ->where($collegeFeeScope);

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('fee_name', 'like', "%{$search}%")
                  ->orWhere('requirement_level', 'like', "%{$search}%")
                  ->orWhereHas('organization', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($organizationId) {
            $baseQuery->where('organization_id', $organizationId);
        }

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
            ->whereHas('fee', function ($q) use ($collegeFeeScope, $search, $organizationId) {
                $q->where($collegeFeeScope);

                if ($search) {
                    $q->where(function ($q) use ($search) {
                        $q->where('fee_name', 'like', "%{$search}%")
                          ->orWhere('requirement_level', 'like', "%{$search}%")
                          ->orWhereHas('organization', function ($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%");
                          });
                    });
                }

                if ($organizationId) {
                    $q->where('organization_id', $organizationId);
                }
            })
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
                ->whereIn('organization_id', $motherOrgIds);

            if ($organizationId) {
                $inheritedFees->where(function ($q) use ($organizationId, $selectedOrg) {
                    $q->where('organization_id', $organizationId);

                    if ($selectedOrg && $selectedOrg->mother_organization_id) {
                        $q->orWhere('organization_id', $selectedOrg->mother_organization_id);
                    }
                });
            }

            if ($search) {
                $inheritedFees->where(function ($q) use ($search) {
                    $q->where('fee_name', 'like', "%{$search}%")
                      ->orWhere('requirement_level', 'like', "%{$search}%")
                      ->orWhereHas('organization', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $inheritedFees = $inheritedFees->get();

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
                        ->where('organization_id', $osaId);

                    if ($search) {
                        $osaFees->where(function ($q) use ($search) {
                            $q->where('fee_name', 'like', "%{$search}%")
                              ->orWhere('requirement_level', 'like', "%{$search}%")
                              ->orWhereHas('organization', function ($q) use ($search) {
                                  $q->where('name', 'like', "%{$search}%");
                              });
                        });
                    }

                    if ($organizationId) {
                        // Only filter OSA fees to this org if it's NOT a child org
                        if (!($selectedOrg && $selectedOrg->mother_organization_id)) {
                            $osaFees->where('organization_id', $organizationId);
                        }
                    }

                    $inheritedFees = $inheritedFees->merge($osaFees->get());
                }
            }
        }

        // Merge, dedupe and order by approved_at (desc)
        $approvedFees = $approvedFees->merge($inheritedFees)->unique('id')->sortByDesc('approved_at')->values();

        return view('college.fees.approval', compact('pendingFees', 'approvedFees', 'pendingRequests','disabledFees', 'tab', 'organizations'));
    }

    /**
     * Show fee details to the College Dean (and allow approve/reject from details).
     */
    public function show(Fee $fee)
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(
            $fee->college_id === $user->college_id ||
            optional($fee->organization)->college_id === $user->college_id,
            403
        );

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

        abort_unless(
            $fee->college_id === $user->college_id ||
            optional($fee->organization)->college_id === $user->college_id,
            403
        );

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

    public function reject(Request $request, Fee $fee)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password verification failed.'])->withInput();
        }

        abort_unless(
            $fee->college_id === $user->college_id ||
            optional($fee->organization)->college_id === $user->college_id,
            403
        );

        if ($fee->status !== 'pending') {
            return back()->with('error', 'Only pending fees can be rejected.');
        }

        $fee->update([
            'status' => 'rejected',
            'approved_at' => now(),
        ]);

        log_activity(
            'Fee Rejected',
            "Fee '{$fee->fee_name}' rejected by dean",
            null, null, null,
            [
                'fee_id' => $fee->id,
                'college_id' => $fee->college_id,
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'previous_status' => 'pending',
                'new_status' => 'rejected',
            ]
        );

        return back()->with('success', 'Fee rejected.');
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
