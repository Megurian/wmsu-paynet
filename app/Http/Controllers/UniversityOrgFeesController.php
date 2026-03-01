<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Document;
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
        $fee->load('appeals', 'organization');

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

        // delete associated documents and their files
        if ($fee->accreditationDocument) {
            Storage::disk('public')->delete($fee->accreditationDocument->file_path);
            $fee->accreditationDocument->delete();
        }
        if ($fee->resolutionDocument) {
            Storage::disk('public')->delete($fee->resolutionDocument->file_path);
            $fee->resolutionDocument->delete();
        }
        if ($fee->supportingDocument) {
            Storage::disk('public')->delete($fee->supportingDocument->file_path);
            $fee->supportingDocument->delete();
        }

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
        $accreditationDocuments = $organization->documents()
            ->where('document_type', 'Accreditation Certification')
            ->latest()
            ->get();
        $resolutionDocuments = $organization->documents()
            ->where('document_type', 'Resolution of Collection')
            ->latest()
            ->get();
        
        return view('university_org.create-fees', compact('organization', 'accreditationDocuments', 'resolutionDocuments'));
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
            'accreditation_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'accreditation_document_id' => 'nullable|exists:documents,id',
            'resolution_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'resolution_document_id' => 'nullable|exists:documents,id',
            'supporting_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
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
            'recurrence' => $request->recurrence,
            'fee_scope' => 'university-wide',
            'status' => 'pending',
        ];

        // ensure resolution file is required if mandatory on server-side too (bare validation requested)
        if ($request->requirement_level === 'mandatory') {
            if (!$request->hasFile('resolution_file') && !$request->resolution_document_id) {
                return back()->withErrors(['resolution_file' => 'Resolution of Collection is required for mandatory fees.'])->withInput();
            }
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

        // Handle resolution file or document (for mandatory fees)
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

        Fee::create($data);

        return redirect()->route('university_org.fees')
            ->with('success', 'Fee created successfully and is pending OSA approval!');
    }

    
}