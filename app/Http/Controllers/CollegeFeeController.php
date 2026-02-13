<?php

namespace App\Http\Controllers;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CollegeFeeController extends Controller
{
    public function index()
    {
        $fees = Fee::where('fee_scope', 'college')
            ->where('college_id', auth()->user()->college_id)
            ->orderByDesc('created_at')
            ->get();

        return view('college.fees.index', compact('fees'));
    }

    public function create()
    {
        return view('college.fees.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fee_name' => 'required|string',
            'purpose' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'requirement_level' => 'required|in:mandatory,optional',
        ]);

        Fee::create([
            ...$data,
            'fee_scope' => 'college',
            'college_id' => auth()->user()->college_id,
            'user_id' => auth()->id(),
            'approval_level' => 'dean',
            'status' => 'pending',
        ]);

        return redirect()->route('college.fees')
            ->with('success', 'Fee submitted for dean approval.');
    }
}
