<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\SchoolYear;
use App\Models\Semester;

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
        // - If the mother organization inherits OSA fees, also include approved fees created by OSA.
        $mother = $organization->motherOrganization;
        $motherId = $mother?->id;

        // Determine OSA org id if exists
        $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');

        $motherInheritsOsaFees = ($mother && $mother->inherits_osa_fees);

        // Local fees for this organization (all statuses)
        $localFees = $organization->fees()->with('organization')->orderBy('created_at', 'desc')->get();

        // Inherited fees: approved fees from mother org, and OSA if applicable (exclude local to avoid duplication)
        $inheritedFees = collect();

        // Only query inherited fees when there is a direct mother org or the mother org inherits OSA fees
        if ($motherId || ($motherInheritsOsaFees && $osaId)) {
            $inheritedQuery = Fee::with('organization')
                ->where('status', 'approved')
                ->where(function($q) use ($motherId, $motherInheritsOsaFees, $osaId) {
                    // Direct mother org's fees (if present)
                    if ($motherId) {
                        $q->where('organization_id', $motherId);
                    }

                    // If the mother org inherits OSA fees, include OSA fees as well
                    if ($motherInheritsOsaFees && $osaId) {
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
        $accreditationDocuments = $organization->documents()
            ->where('document_type', 'Accreditation Certification')
            ->latest()
            ->get();
        $resolutionDocuments = $organization->documents()
            ->where('document_type', 'Resolution of Collection')
            ->latest()
            ->get();
        
        return view('college_org.create-fee', compact('organization', 'accreditationDocuments', 'resolutionDocuments'));
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
            'accreditation_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'accreditation_document_id' => 'nullable|exists:documents,id',
            'resolution_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'resolution_document_id' => 'nullable|exists:documents,id',
            'supporting_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $organization = Auth::user()->organization;
        if (!$organization) {
            return redirect()->route('college_org.fees')->with('error', 'Organization not found.');
        }

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        // All college-local fees start with dean approval.
        // Fees created by `college_org` (and child offices) require Dean -> OSA approval flow.
        $data = [
            'organization_id' => $organization->id,
            'user_id' => Auth::id(),
            'fee_name' => $request->fee_name,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'amount' => $request->amount,
            'remittance_percent' => $request->remittance_percent ?? null,
            'requirement_level' => $request->requirement_level,
            'recurrence' => $request->recurrence,
            'status' => 'pending',
            'fee_scope' => 'college',
            'college_id' => $organization->college_id,
            'approval_level' => 'dean',
        ];

         $data['created_school_year_id'] = $activeSY->id;
        $data['created_semester_id'] = $activeSem->id;

        if ($request->requirement_level === 'mandatory' && !$request->hasFile('resolution_file') && !$request->resolution_document_id) {
            return back()->withErrors(['resolution_file' => 'Resolution of Collection is required for mandatory fees.'])->withInput();
        }

        // Handle accreditation file or document
        if ($request->hasFile('accreditation_file')) {
            $file = $request->file('accreditation_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("documents/{$organization->id}", $fileName, 'public');
            
            // Create document record
            $document = Document::create([
                'organization_id' => $organization->id,
                'document_type' => 'Accreditation Certification',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'original_file_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
            
            $data['accreditation_document_id'] = $document->id;
        } elseif ($request->accreditation_document_id) {
            $data['accreditation_document_id'] = $request->accreditation_document_id;
        }

        // Handle resolution file or document
        if ($request->hasFile('resolution_file')) {
            $file = $request->file('resolution_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("documents/{$organization->id}", $fileName, 'public');
            
            // Create document record
            $document = Document::create([
                'organization_id' => $organization->id,
                'document_type' => 'Resolution of Collection',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'original_file_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
            
            $data['resolution_document_id'] = $document->id;
        } elseif ($request->resolution_document_id) {
            $data['resolution_document_id'] = $request->resolution_document_id;
        }

        // Handle supporting file (optional)
        if ($request->hasFile('supporting_file')) {
            $file = $request->file('supporting_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("documents/{$organization->id}", $fileName, 'public');
            
            // Create document record for supporting document
            $document = Document::create([
                'organization_id' => $organization->id,
                'document_type' => 'Supporting Document',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'original_file_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
            
            $data['supporting_document_id'] = $document->id;
        }

        \App\Models\Fee::create($data);

        // For college organization submissions the workflow is: Dean → OSA
        return redirect()->route('college_org.fees')->with('success', 'Fee created successfully and is pending Dean approval (then OSA).');
    }

    public function show(\App\Models\Fee $fee)
    {
        $organization = Auth::user()->organization;
        $allowedOrgIds = [$organization->id];
        if ($organization->motherOrganization) $allowedOrgIds[] = $organization->motherOrganization->id;
        if ($organization->motherOrganization && $organization->motherOrganization->inherits_osa_fees) {
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
        $fee->recurrence = $request->recurrence;

        $organization = Auth::user()->organization;

        if ($request->hasFile('accreditation_file')) {
            $file = $request->file('accreditation_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("documents/{$organization->id}", $fileName, 'public');
            $doc = Document::create([
                'organization_id' => $organization->id,
                'document_type' => 'Accreditation Certification',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'original_file_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
            $fee->accreditation_document_id = $doc->id;
        }

        if ($request->hasFile('resolution_file')) {
            $file = $request->file('resolution_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("documents/{$organization->id}", $fileName, 'public');
            $doc = Document::create([
                'organization_id' => $organization->id,
                'document_type' => 'Resolution of Collection',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'original_file_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
            $fee->resolution_document_id = $doc->id;
        }

        $fee->save();

        return redirect()->route('college_org.fees')->with('success', 'Fee updated successfully.');
    }

    public function destroy(\App\Models\Fee $fee)
    {
        $organization = Auth::user()->organization;
        if ($fee->organization_id !== $organization->id) abort(403);
        if ($fee->status === 'approved') return back()->with('error', 'Approved fees cannot be deleted.');

        // delete associated documents and their files
        if ($fee->accreditationDocument) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($fee->accreditationDocument->file_path);
            $fee->accreditationDocument->delete();
        }
        if ($fee->resolutionDocument) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($fee->resolutionDocument->file_path);
            $fee->resolutionDocument->delete();
        }
        if ($fee->supportingDocument) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($fee->supportingDocument->file_path);
            $fee->supportingDocument->delete();
        }

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