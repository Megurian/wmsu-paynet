<?php

namespace App\Http\Controllers;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollegeFeeApprovalController extends Controller
{
    public function index()
    {

        $fees = Fee::where('fee_scope', 'college')
            ->where('college_id', auth()->user()->college_id)
            ->where('status', 'pending')
            ->get();

        return view('college.fees.approval', compact('fees'));
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
