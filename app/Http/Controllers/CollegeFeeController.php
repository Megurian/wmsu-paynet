<?php

namespace App\Http\Controllers;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\SchoolYear;
use App\Models\Semester;
class CollegeFeeController extends Controller
{
    public function index(Request $request)
    {
        $collegeId = auth()->user()->college_id;
        $search = $request->input('search');
        $organizationId = $request->input('organization_id');

        $organizations = \App\Models\Organization::query()
            ->where(function ($query) use ($collegeId) {
                $query->whereNull('college_id')
                      ->orWhere('college_id', $collegeId);
            })
            ->orderBy('name')
            ->get();

        $selectedChildOrg = null;
        if ($organizationId) {
            $selectedChildOrg = \App\Models\Organization::where('id', $organizationId)
                ->where('college_id', $collegeId)
                ->whereNotNull('mother_organization_id')
                ->first();
        }

        // Get base college fees, including fees scoped to college whose organization belongs to this college.
        $baseFees = Fee::with(['organization.motherOrganization'])
            ->where('fee_scope', 'college')
            ->where(function ($query) use ($collegeId) {
                $query->where('college_id', $collegeId)
                      ->orWhereHas('organization', function ($query) use ($collegeId) {
                          $query->where('college_id', $collegeId);
                      });
            });

        if ($organizationId) {
            $baseFees->where('organization_id', $organizationId);
        }

        if ($search) {
            $baseFees->where(function ($q) use ($search) {
                $q->where('fee_name', 'like', "%{$search}%")
                  ->orWhere('requirement_level', 'like', "%{$search}%")
                  ->orWhereHas('organization', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $baseFees = $baseFees->orderByDesc('created_at')->get();

        // Include fees inherited by child organizations under this college
        $childOrgs = \App\Models\Organization::where('college_id', $collegeId)
            ->whereNotNull('mother_organization_id')
            ->with('motherOrganization')
            ->get();

        $motherOrgIds = $childOrgs->pluck('mother_organization_id')->unique()->filter()->values()->all();

        $inheritedFees = collect();

        if (!empty($motherOrgIds)) {
            $inheritedFees = Fee::with(['organization.motherOrganization'])
                ->whereIn('organization_id', $motherOrgIds);

            if ($organizationId) {
                $inheritedFees->where(function ($q) use ($organizationId, $selectedChildOrg) {
                    $q->where('organization_id', $organizationId);

                    if ($selectedChildOrg) {
                        $q->orWhere('organization_id', $selectedChildOrg->mother_organization_id);
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
                    $osaFees = Fee::with(['organization.motherOrganization'])
                        ->where('organization_id', $osaId);

                    if ($organizationId) {
                        if (! $selectedChildOrg) {
                            $osaFees->where('organization_id', $organizationId);
                        }
                    }

                    if ($search) {
                        $osaFees->where(function ($q) use ($search) {
                            $q->where('fee_name', 'like', "%{$search}%")
                              ->orWhere('requirement_level', 'like', "%{$search}%")
                              ->orWhereHas('organization', function ($q) use ($search) {
                                  $q->where('name', 'like', "%{$search}%");
                              });
                        });
                    }

                    $inheritedFees = $inheritedFees->merge($osaFees->get());
                }
            }
        }

        // Merge, dedupe and order by created_at (desc)
        $fees = $baseFees->merge($inheritedFees)->unique('id')->sortByDesc('created_at')->values();

        return view('college.fees.index', compact('fees', 'organizations'));
    }

    public function create()
    {
        return view('college.fees.create');

    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $data = $request->validate([
            'fee_name' => 'required|string',
            'purpose' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'requirement_level' => 'required|in:mandatory,optional',
            'recurrence' => 'required|in:one_time,semestrial,annual',
        ]);

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $fee = Fee::create([
            ...$data,
            'recurrence' => $data['recurrence'] ?? 'one_time',
            'fee_scope' => 'college',
            'college_id' => auth()->user()->college_id,
            'user_id' => auth()->id(),
            'approval_level' => 'dean',
            'status' => 'pending',
            'created_school_year_id' => $activeSY?->id,
        'created_semester_id' => $activeSem?->id,
        ]);

        log_activity(
        'Created Fee',
        "Fee '{$fee->fee_name}' created and submitted for dean approval",
        null, null, null,
        [
            'fee_id' => $fee->id,
            'college_id' => $user->college_id,
            'fee_scope' => 'college',
            'amount' => $fee->amount,
            'requirement_level' => $fee->requirement_level,
            'recurrence' => $fee->recurrence,
            'status' => 'pending',
            'approval_level' => 'dean',
            'school_year_id' => $activeSY?->id,
            'semester_id' => $activeSem?->id,
            'created_by' => $user->id,
            'action_by' => $user->id,
        ]
    );
        return redirect()->route('college.fees')
            ->with('success', 'Fee submitted for dean approval.');
    }
}
