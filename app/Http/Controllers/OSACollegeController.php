<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\College;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OSACollegeController extends Controller
{
    // List all colleges
    public function index()
    {
        $colleges = College::all();
        return view('osa.college', compact('colleges'));
    }

    // Show create college form
    public function create()
    {
        return view('osa.create-college');
    }

    public function show($id)
    {
        $college = College::with(['admins' => function($query){
            $query->where('role', 'college');
        }])->findOrFail($id);


        // Placeholder for organizations, etc.
        $organizations = []; // Replace with real data later

        return view('osa.college-details', compact('college', 'organizations'));
    }


    // Store a new college and initial admin
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'college_code' => 'required|string|max:20|unique:colleges,college_code',
            'logo' => 'nullable|image|max:2048', // optional
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($request) {

            // Handle logo upload
            $logoPath = $request->file('logo') ? $request->file('logo')->store('colleges', 'public') : null;

            // Create college with provided code
            $college = College::create([
                'name' => $request->name,
                'college_code' => strtoupper($request->college_code),
                'logo' => $logoPath,
            ]);

            // Create initial college admin
            User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'college',
                'college_id' => $college->id,
            ]);
        });

        return redirect()->route('osa.college')->with('status', 'College and initial admin created successfully!');
    }

    public function destroy($id)
    {
        $college = College::findOrFail($id);

        // Optional: delete college admins if you want
        $college->users()->delete();

        // Delete college
        $college->delete();

        return redirect()->route('osa.college')->with('status', 'College deleted successfully!');
    }

}
