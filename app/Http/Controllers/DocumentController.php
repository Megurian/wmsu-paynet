<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents for the organization.
     */
    public function index($role)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if (! in_array($role, ['university_org', 'college_org'], true) || ! $user->organization_id) {
            abort(403, 'Unauthorized access');
        }

        $organization = Organization::where('id', $user->organization_id)
            ->where('role', $role)
            ->firstOrFail();

        $documents = $organization->documents()->with('uploadedBy')->latest()->get();

        return view('documents.index', [
            'organization' => $organization,
            'documents' => $documents,
            'role' => $role,
        ]);
    }

    /**
     * Store a new document.
     */
    public function store(Request $request, $role)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if (! in_array($role, ['university_org', 'college_org'], true) || ! $user->organization_id) {
            abort(403, 'Unauthorized access');
        }

        $organization = Organization::where('id', $user->organization_id)
            ->where('role', $role)
            ->firstOrFail();

        // Validate the request
        $validated = $request->validate([
            'document_type' => 'required|in:Accreditation Certification,Resolution of Collection,Supporting Document',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        // Check if a document of this type already exists (only for non-supporting documents)
        // Supporting documents are 1:1 with fees, so they're created separately
        if ($validated['document_type'] !== 'Supporting Document') {
            $existingDocument = Document::where('organization_id', $organization->id)
                ->where('document_type', $validated['document_type'])
                ->first();

            // Delete the old file if replacing
            if ($existingDocument) {
                Storage::disk('public')->delete($existingDocument->file_path);
                $existingDocument->delete();
            }
        }

        // Store the file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        // Create the document record
        Document::create([
            'organization_id' => $organization->id,
            'document_type' => $validated['document_type'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'original_file_name' => $file->getClientOriginalName(),
            'uploaded_by' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully!');
    }

    /**
     * Preview/download the document.
     */
    public function preview(Document $document)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($document->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized access');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        return response()->file(
            Storage::disk('public')->path($document->file_path),
            [
                'Content-Disposition' => 'inline; filename="' . $document->original_file_name . '"',
            ]
        );
    }

    /**
     * Create a supporting document for a fee (internal use).
     */
    public function createSupportingDocument($file, $organizationId, $userId)
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs("documents/{$organizationId}", $fileName, 'public');

        return Document::create([
            'organization_id' => $organizationId,
            'document_type' => 'Supporting Document',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'original_file_name' => $file->getClientOriginalName(),
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Delete a document.
     */
    public function destroy(Document $document)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($document->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized access');
        }

        // Delete the file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete the database record
        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully!');
    }
}


