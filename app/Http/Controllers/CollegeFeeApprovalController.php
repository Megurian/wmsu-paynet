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

        $pendingFees = Fee::where('fee_scope', 'college')
            ->where('college_id', $collegeId)
            ->where('status', 'pending')
            ->where('approval_level', 'dean')
            ->get();

        $approvedFees = Fee::where('fee_scope', 'college')
            ->where('college_id', $collegeId)
            ->where('status', 'approved')
            ->where('approval_level', 'dean')
            ->get();

        return view('college.fees.approval', compact('pendingFees', 'approvedFees', 'tab'));
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