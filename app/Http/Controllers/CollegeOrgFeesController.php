<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollegeOrgFeesController extends Controller
{
    public function index(Request $request)
    {
        $organization = Auth::user()->organization;
        if (!$organization) {
            return redirect()->route('dashboard')->with('error', 'Organization not found.');
        }

        // Collect approved fees according to rules:
        // - Always include this organization's approved fees.
        // - Include fees from this org's direct mother organization (if any), both mandatory and optional.
        // - ONLY if the mother organization is 'USC' (org_code == 'USC'), also include approved fees created by OSA.
        $mother = $organization->motherOrganization;
        $motherId = $mother?->id;

        // Determine OSA org id if exists
        $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');

        $isChildOfUSC = ($mother && $mother->org_code === 'USC');

        // Local fees for this organization (all statuses)
        $localFees = $organization->fees()->with('organization')->orderBy('created_at', 'desc')->get();

        // Inherited fees: approved fees from mother org, and OSA if applicable (exclude local to avoid duplication)
        $inheritedFees = collect();

        // Only query inherited fees when there is a direct mother org or the college is a child of USC (include OSA)
        if ($motherId || ($isChildOfUSC && $osaId)) {
            $inheritedQuery = Fee::with('organization')
                ->where('status', 'approved')
                ->where(function($q) use ($motherId, $isChildOfUSC, $osaId) {
                    // Direct mother org's fees (if present)
                    if ($motherId) {
                        $q->where('organization_id', $motherId);
                    }

                    // If this college is a child of USC, include OSA fees as well
                    if ($isChildOfUSC && $osaId) {
                        $q->orWhere('organization_id', $osaId);
                    }
                })
                ->where('organization_id', '!=', $organization->id)
                ->orderBy('created_at', 'desc');

            $inheritedFees = $inheritedQuery->get();
        }

        // Merge local and inherited, avoid duplicates, sort by created_at desc
        $fees = $localFees->merge($inheritedFees)->unique('id')->sortByDesc('created_at')->values();

        return view('college_org.fees', compact('fees', 'organization'));
    }

    public function create()
    {
        $organization = Auth::user()->organization;
        return view('college_org.create-fee', compact('organization'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fee_name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'remittance_percent' => 'nullable|numeric|min:0|max:100',
            'requirement_level' => 'required|in:mandatory,optional',
            'accreditation_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'resolution_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $organization = Auth::user()->organization;
        if (!$organization) {
            return redirect()->route('college_org.fees')->with('error', 'Organization not found.');
        }

        $data = [
            'organization_id' => $organization->id,
            'user_id' => Auth::id(),
            'fee_name' => $request->fee_name,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'amount' => $request->amount,
            'remittance_percent' => $request->remittance_percent ?? null,
            'requirement_level' => $request->requirement_level,
            'status' => 'pending',
        ];

        if ($request->requirement_level === 'mandatory' && !$request->hasFile('resolution_file')) {
            return back()->withErrors(['resolution_file' => 'Resolution of Collection is required for mandatory fees.'])->withInput();
        }

        if ($request->hasFile('accreditation_file')) {
            $data['accreditation_file'] = $request->file('accreditation_file')->store('fees/accreditation', 'public');
        }

        if ($request->hasFile('resolution_file')) {
            $data['resolution_file'] = $request->file('resolution_file')->store('fees/resolution', 'public');
        }

        \App\Models\Fee::create($data);

        return redirect()->route('college_org.fees')->with('success', 'Fee created successfully and is pending OSA approval');
    }

    public function show(\App\Models\Fee $fee)
    {
        $organization = Auth::user()->organization;
        $allowedOrgIds = [$organization->id];
        if ($organization->motherOrganization) $allowedOrgIds[] = $organization->motherOrganization->id;
        if ($organization->motherOrganization && $organization->motherOrganization->org_code === 'USC') {
            $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
            if ($osaId) $allowedOrgIds[] = $osaId;
        }

        if (!in_array($fee->organization_id, $allowedOrgIds) && $fee->organization_id !== $organization->id) {
            abort(403);
        }

        $fee->load('appeals');
        return view('college_org.fee-details', compact('organization', 'fee'));
    }

    public function edit(\App\Models\Fee $fee)
    {
        $organization = Auth::user()->organization;
        if ($fee->organization_id !== $organization->id) abort(403);
        if ($fee->status === 'approved') {
            return redirect()->route('college_org.fees')->with('error', 'Approved fees cannot be edited.');
        }
        return view('college_org.edit-fee', compact('organization', 'fee'));
    }

    public function update(Request $request, \App\Models\Fee $fee)
    {
        $organization = Auth::user()->organization;
        if ($fee->organization_id !== $organization->id) abort(403);
        if ($fee->status === 'approved') {
            return redirect()->route('college_org.fees')->with('error', 'Approved fees cannot be modified.');
        }

        $request->validate([
            'fee_name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'remittance_percent' => 'nullable|numeric|min:0|max:100',
            'requirement_level' => 'required|in:mandatory,optional',
            'accreditation_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'resolution_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $fee->fee_name = $request->fee_name;
        $fee->purpose = $request->purpose;
        $fee->description = $request->description;
        $fee->amount = $request->amount;
        $fee->remittance_percent = $request->remittance_percent ?? null;
        $fee->requirement_level = $request->requirement_level;

        if ($request->hasFile('accreditation_file')) {
            if ($fee->accreditation_file) \Illuminate\Support\Facades\Storage::disk('public')->delete($fee->accreditation_file);
            $fee->accreditation_file = $request->file('accreditation_file')->store('fees/accreditation', 'public');
        }

        if ($request->hasFile('resolution_file')) {
            if ($fee->resolution_file) \Illuminate\Support\Facades\Storage::disk('public')->delete($fee->resolution_file);
            $fee->resolution_file = $request->file('resolution_file')->store('fees/resolution', 'public');
        }

        $fee->save();

        return redirect()->route('college_org.fees')->with('success', 'Fee updated successfully.');
    }

    public function destroy(\App\Models\Fee $fee)
    {
        $organization = Auth::user()->organization;
        if ($fee->organization_id !== $organization->id) abort(403);
        if ($fee->status === 'approved') return back()->with('error', 'Approved fees cannot be deleted.');

        if ($fee->accreditation_file) \Illuminate\Support\Facades\Storage::disk('public')->delete($fee->accreditation_file);
        if ($fee->resolution_file) \Illuminate\Support\Facades\Storage::disk('public')->delete($fee->resolution_file);

        $fee->delete();

        return redirect()->route('college_org.fees')->with('success', 'Fee deleted successfully.');
    }

    public function submitAppeal(Request $request, \App\Models\Fee $fee)
    {
        $organization = Auth::user()->organization;
        if ($fee->organization_id !== $organization->id && ($organization->motherOrganization?->id ?? null) !== $fee->organization_id) {
            abort(403);
        }

        if ($fee->status !== 'disabled') return back()->with('error', 'Appeals are only allowed for disabled fees.');

        $request->validate([
            'reason' => 'required|string|max:2000',
            'supporting_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
            'supporting_files' => 'nullable|array|max:10',
        ]);

        $files = [];
        if ($request->hasFile('supporting_files')) {
            foreach ($request->file('supporting_files') as $file) {
                $path = $file->store("fees/appeals/{$fee->id}", 'public');
                $files[] = $path;
            }
        }

        $appeal = \App\Models\Appeal::create([
            'fee_id' => $fee->id,
            'user_id' => Auth::id(),
            'reason' => $request->reason,
            'supporting_files' => $files ?: null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Appeal submitted. OSA will review and respond soon.');
    }
}
