<?php

namespace App\Http\Controllers;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CollegeFeeController extends Controller
{
    public function index()
    {
        $collegeId = auth()->user()->college_id;

        // Get base college fees
        $baseFees = Fee::with(['organization.motherOrganization'])
            ->where('fee_scope', 'college')
            ->where('college_id', $collegeId)
            ->orderByDesc('created_at')
            ->get();

        // Include fees inherited by child organizations under this college
        $childOrgs = \App\Models\Organization::where('college_id', $collegeId)
            ->whereNotNull('mother_organization_id')
            ->with('motherOrganization')
            ->get();

        $motherOrgIds = $childOrgs->pluck('mother_organization_id')->unique()->filter()->values()->all();

        $inheritedFees = collect();

        if (!empty($motherOrgIds)) {
            $inheritedFees = Fee::with(['organization.motherOrganization'])
                ->whereIn('organization_id', $motherOrgIds)
                ->get();

            // Special-case: include OSA fees if any child is a child of USC (they inherit OSA fees)
            $hasUSCChild = $childOrgs->firstWhere('motherOrganization.org_code', 'USC');
            if ($hasUSCChild) {
                $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
                if ($osaId) {
                    $osaFees = Fee::with(['organization.motherOrganization'])
                        ->where('organization_id', $osaId)
                        ->get();

                    $inheritedFees = $inheritedFees->merge($osaFees);
                }
            }
        }

        // Merge, dedupe and order by created_at (desc)
        $fees = $baseFees->merge($inheritedFees)->unique('id')->sortByDesc('created_at')->values();

        return view('college.fees.index', compact('fees'));
    }

    public function create()
    {
        return view('college.fees.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fee_name' => 'required|string',
            'purpose' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'requirement_level' => 'required|in:mandatory,optional',
            'recurrence' => 'required|in:one_time,semestrial,annual',
        ]);

        Fee::create([
            ...$data,
            'recurrence' => $data['recurrence'] ?? 'one_time',
            'fee_scope' => 'college',
            'college_id' => auth()->user()->college_id,
            'user_id' => auth()->id(),
            'approval_level' => 'dean',
            'status' => 'pending',
        ]);

        return redirect()->route('college.fees')
            ->with('success', 'Fee submitted for dean approval.');
    }
}
