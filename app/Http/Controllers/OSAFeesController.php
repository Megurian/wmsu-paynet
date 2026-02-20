<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Organization;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OSAFeesController extends Controller
{
    public function index(Request $request)
    {
        // show pending fees for OSA to review (includes fees with pending appeals)
       $pendingFees = Fee::with(['organization', 'user', 'appeals'])
        ->where(function($q) {
            $q->where(function($q2) {
                // Fees specifically pending for OSA (organization or university-wide)
                $q2->whereIn('fee_scope', ['organization', 'university-wide'])
                ->where('status', 'pending');
            })
            ->orWhere(function($q3) {
                // College fees assigned to OSA
                $q3->where('fee_scope', 'college')
                ->where('approval_level', 'osa')
                ->where('status', 'pending');
            })
            ->orWhereHas('appeals', function($q4) {
                $q4->where('status', 'pending');
            });
        })
        ->orderBy('created_at', 'desc')
        ->get();

        // Status filter (default to approved)
        $status = $request->get('status', 'approved');

// Show university-wide fees and college-organization fees (exclude college-local/student-coordinator entries)
        $filteredQuery = Fee::with(['organization', 'user'])
            ->where(function($q) {
                // university-wide fees (OSA and university orgs)
                $q->where('fee_scope', 'university-wide')
                  // college fees that are tied to a college organization (organization_id NOT NULL)
                  ->orWhere(function($q2) {
                      $q2->where('fee_scope', 'college')
                         ->whereNotNull('organization_id');
                  });
            });

        if ($status === 'approved') {
            $filteredQuery->where('status', 'approved')
                ->whereDoesntHave('appeals', function($q) { $q->where('status', 'pending'); });
        } elseif ($status === 'pending') {
            $filteredQuery->where(function($q) {
                $q->where('status', 'pending')
                  ->orWhereHas('appeals', function($q2) { $q2->where('status', 'pending'); });
            });
        } elseif ($status === 'disabled') {
            $filteredQuery->where('status', 'disabled');
        } // 'all' or any other value will not apply a status filter

        // Other filters
        if ($request->filled('organization_id')) {
            $filteredQuery->where('organization_id', $request->organization_id);
        }

        if ($request->filled('requirement_level')) {
            $filteredQuery->where('requirement_level', $request->requirement_level);
        }

        if ($request->filled('recurrence')) {
            $filteredQuery->where('recurrence', $request->recurrence);
        }

        if ($request->filled('organization_role')) {
            // organizations have a role column
            $orgIds = Organization::where('role', $request->organization_role)->pluck('id');
            $filteredQuery->whereIn('organization_id', $orgIds);
        }

        $filteredFees = $filteredQuery->orderBy('created_at', 'desc')->get();

        $organizations = Organization::orderBy('name')->get();

        return view('osa.fees', compact('pendingFees', 'filteredFees', 'organizations', 'status'));
    }

    public function create()
    {
        // Ensure an Organization row exists for OSA so it can be selected as an organization
        $osaOrg = Organization::firstOrCreate(
            ['org_code' => 'OSA'],
            ['name' => 'Office of Student Affairs', 'role' => 'university_org']
        );

        $organizations = Organization::orderBy('name')->get();
        return view('osa.create-fee', compact('organizations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'fee_name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'remittance_percent' => 'nullable|numeric|min:0|max:100',
            'requirement_level' => 'required|in:mandatory,optional',
            'recurrence' => 'required|in:one_time,semestrial,annual',
            'legal_basis_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Determine fee_scope according to the organization's role
        $org = \App\Models\Organization::find($request->organization_id);
        $feeScope = 'organization';
        if ($org?->role === 'university_org') {
            $feeScope = 'university-wide';
        } elseif ($org?->role === 'college_org') {
            $feeScope = 'college';
        }

        $data = [
            'organization_id' => $request->organization_id,
            'user_id' => Auth::id(),
            'fee_name' => $request->fee_name,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'amount' => $request->amount,
            'remittance_percent' => $request->remittance_percent ?? null,
            'requirement_level' => $request->requirement_level,
            'recurrence' => $request->recurrence,
            'fee_scope' => $feeScope,
            // OSA-created fees are auto-approved
            'status' => 'approved',
        ];

        // Store legal basis as a Resolution of Collection document
        if ($request->hasFile('legal_basis_file')) {
            $file = $request->file('legal_basis_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("documents/{$org->id}", $fileName, 'public');
            $doc = Document::create([
                'organization_id' => $org->id,
                'document_type' => 'Resolution of Collection',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'original_file_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
            $data['resolution_document_id'] = $doc->id;
        }

        // Accreditation is optional and not required for OSA
        if ($request->hasFile('accreditation_file')) {
            $file = $request->file('accreditation_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("documents/{$org->id}", $fileName, 'public');
            $doc = Document::create([
                'organization_id' => $org->id,
                'document_type' => 'Accreditation Certification',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'original_file_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
            $data['accreditation_document_id'] = $doc->id;
        }

        Fee::create($data);

        return redirect()->route('osa.fees')->with('success', 'Fee created and auto-approved.');
    }

    public function show(Fee $fee)
    {
        // OSA can view any fee including pending ones
        $fee->load('organization', 'user', 'appeals.user');
        return view('osa.fee-details', compact('fee'));
    }

    /**
     * Accept an appeal: mark appeal accepted and set fee back to pending for re-approval.
     */
    public function acceptAppeal(Request $request, \App\Models\Appeal $appeal)
    {
        $request->validate([
            'remark' => 'required|string|max:1000',
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password verification failed.'])->withInput();
        }

        if ($appeal->status !== 'pending') {
            return back()->with('error', 'Only pending appeals can be accepted.');
        }

        $appeal->status = 'accepted';
        $appeal->review_remark = $request->remark;
        $appeal->reviewed_by = $user->id;
        $appeal->reviewed_at = now();
        $appeal->save();

        // Set fee back to pending for OSA to re-approve
        $fee = $appeal->fee;
        $fee->status = 'pending';
        $fee->save();

        return back()->with('success', 'Appeal accepted and fee set to pending.');
    }

    /**
     * Reject an appeal with mandatory remark and password verification.
     */
    public function rejectAppeal(Request $request, \App\Models\Appeal $appeal)
    {
        $request->validate([
            'remark' => 'required|string|max:1000',
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password verification failed.'])->withInput();
        }

        if ($appeal->status !== 'pending') {
            return back()->with('error', 'Only pending appeals can be rejected.');
        }

        $appeal->status = 'rejected';
        $appeal->review_remark = $request->remark;
        $appeal->reviewed_by = $user->id;
        $appeal->reviewed_at = now();
        $appeal->save();

        return back()->with('success', 'Appeal rejected.');
    }

    /**
     * Approve a fee after verifying OSA user's password.
     */
    public function approve(Request $request, Fee $fee)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        // Verify password
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password verification failed.'])->withInput();
        }

        if ($fee->status !== 'pending') {
            return back()->with('error', 'Only pending fees can be approved.');
        }

        $fee->status = 'approved';
        $fee->save();

        return redirect()->route('osa.fees')->with('success', 'Fee approved successfully.');
    }

    /**
     * Disable a fee (mark as disabled). Password confirmation required.
     */
    public function disable(Request $request, Fee $fee)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password verification failed.'])->withInput();
        }

        if ($fee->status === 'disabled') {
            return back()->with('error', 'Fee is already disabled.');
        }

        $fee->status = 'disabled';
        $fee->save();

        return redirect()->route('osa.fees')->with('success', 'Fee has been disabled.');
    }
}
