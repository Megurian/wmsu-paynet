<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UniversityOrgFeesController extends Controller
{
    public function index()
    {
        return view('university_org.fees');
    }

    public function create()
    {
        return view('university_org.create-fees');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fee_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // Logic to store the fee in the database
        // Fee::create($request->all());

        return redirect()->route('university_org.fees')->with('success', 'Fee created successfully!');
    }
}