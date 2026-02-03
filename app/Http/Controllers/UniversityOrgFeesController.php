<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UniversityOrgFeesController extends Controller
{
    public function index()
    {
        $organization = Auth::user()->organization;
        if (!$organization) {
            return redirect()->route('dashboard')->with('error', 'Organization not found.');
        }

        $fees = $organization->fees()->with('user')->orderBy('created_at', 'desc')->get();

        return view('university_org.fees', compact('organization', 'fees'));
    }

    /**
     * Display the details for a single fee for the current organization.
     */
    public function show(Fee $fee)
    {
        $organization = Auth::user()->organization;
        if (!$organization || $fee->organization_id !== $organization->id) {
            abort(403);
        }

        // load appeals for display if needed
        $fee->load('appeals');

        return view('university_org.fee-details', compact('organization', 'fee'));
    }

    public function edit(Fee $fee)
    {
        $organization = Auth::user()->organization;
        if (!$organization || $fee->organization_id !== $organization->id) {
            abort(403);
        }

        if ($fee->status === 'approved') {
            return redirect()->route('university_org.fees.show', $fee->id)->with('error', 'Approved fees cannot be edited.');
        }

        return view('university_org.edit-fee', compact('organization', 'fee'));
    }

    public function update(Request $request, Fee $fee)
    {
        $organization = Auth::user()->organization;
        if (!$organization || $fee->organization_id !== $organization->id) {
            abort(403);
        }

        if ($fee->status === 'approved') {
            return redirect()->route('university_org.fees.show', $fee->id)->with('error', 'Approved fees cannot be modified.');
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
            // delete old if exists
            if ($fee->accreditation_file) Storage::disk('public')->delete($fee->accreditation_file);
            $fee->accreditation_file = $request->file('accreditation_file')->store('fees/accreditation', 'public');
        }

        if ($request->hasFile('resolution_file')) {
            if ($fee->resolution_file) Storage::disk('public')->delete($fee->resolution_file);
            $fee->resolution_file = $request->file('resolution_file')->store('fees/resolution', 'public');
        }

        $fee->save();

        return redirect()->route('university_org.fees.show', $fee->id)->with('success', 'Fee updated successfully.');
    }

    public function destroy(Fee $fee)
    {
        $organization = Auth::user()->organization;
        if (!$organization || $fee->organization_id !== $organization->id) {
            abort(403);
        }

        if ($fee->status === 'approved') {
            return back()->with('error', 'Approved fees cannot be deleted.');
        }

        // delete files
        if ($fee->accreditation_file) Storage::disk('public')->delete($fee->accreditation_file);
        if ($fee->resolution_file) Storage::disk('public')->delete($fee->resolution_file);

        $fee->delete();

        return redirect()->route('university_org.fees')->with('success', 'Fee deleted successfully.');
    }

    /**
     * Submit an appeal when a fee is disabled.
     */
    public function submitAppeal(Request $request, Fee $fee)
    {
        $organization = Auth::user()->organization;
        if (!$organization || $fee->organization_id !== $organization->id) {
            abort(403);
        }

        if ($fee->status !== 'disabled') {
            return back()->with('error', 'Appeals are only allowed for disabled fees.');
        }

        $request->validate([
            'reason' => 'required|string|max:2000',
            'supporting_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
            'supporting_files' => 'nullable|array|max:10',
        ]);

        $files = [];
        if ($request->hasFile('supporting_files')) {
            foreach ($request->file('supporting_files') as $file) {
                // store each file under fees/appeals/{fee_id}
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

    public function create()
    {
        $organization = Auth::user()->organization;
        return view('university_org.create-fees', ['organization' => $organization]);
    }

    public function store(Request $request)
    {
        // Simple validation
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

        // Get the organization from authenticated user
        $organization = Auth::user()->organization;

        if (!$organization) {
            return redirect()->route('university_org.fees')
                ->with('error', 'Organization not found.');
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

        // ensure resolution file is required if mandatory on server-side too (bare validation requested)
        if ($request->requirement_level === 'mandatory') {
            if (!$request->hasFile('resolution_file')) {
                return back()->withErrors(['resolution_file' => 'Resolution of Collection is required for mandatory fees.'])->withInput();
            }
        }

        // Handle accreditation file
        if ($request->hasFile('accreditation_file')) {
            $file = $request->file('accreditation_file');
            $path = $file->store('fees/accreditation', 'public');
            $data['accreditation_file'] = $path;
        }

        // Handle resolution file (for mandatory fees)
        if ($request->hasFile('resolution_file')) {
            $file = $request->file('resolution_file');
            $path = $file->store('fees/resolution', 'public');
            $data['resolution_file'] = $path;
        }

        Fee::create($data);

        return redirect()->route('university_org.fees')
            ->with('success', 'Fee created successfully and is pending OSA approval!');
    }
}