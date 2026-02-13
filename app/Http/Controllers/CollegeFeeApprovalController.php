<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\Request;

class CollegeFeeApprovalController extends Controller
{
    public function index(Request $request)
    {
        $collegeId = auth()->user()->college_id;
        $tab = $request->get('tab', 'pending');

        $baseQuery = Fee::with(['organization.motherOrganization'])
            ->where('fee_scope', 'college')
            ->where('college_id', $collegeId);

        $pendingFees = (clone $baseQuery)
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->where('approval_level', 'dean')
                ->orWhereNull('organization_id'); 
            })
            ->whereDoesntHave('organization', function ($q) {
                $q->whereNotNull('mother_organization_id');
            })
            ->orderByDesc('created_at')
            ->get();

        $allFees = (clone $baseQuery)
            ->where('status', 'approved')
            ->orderByDesc('approved_at')
            ->get();

        return view('college.fees.approval', compact('pendingFees', 'allFees', 'tab'));
    }

    public function approve(Fee $fee)
    {
        abort_unless($fee->college_id === auth()->user()->college_id, 403);

        $fee->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Fee approved.');
    }

    public function reject(Fee $fee)
    {
        abort_unless($fee->college_id === auth()->user()->college_id, 403);

        $fee->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Fee rejected.');
    }
}
