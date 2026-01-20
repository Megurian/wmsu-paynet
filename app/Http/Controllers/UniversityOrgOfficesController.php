<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;

class UniversityOrgOfficesController extends Controller
{
    public function index(Organization $organization)
    {
        return view('university_org.offices', compact('organization'));
    }

    public function create()
    {
        return view('university_org.create-offices');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string',
            'name' => 'required|string|max:255',
            'org_code' => 'required|string|max:255|unique:organizations,org_code',
            'logo' => 'nullable|image',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        // Handle file upload if logo is provided
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // Create the organization
        $organization = Organization::create([
            'role' => $validated['role'],
            'name' => $validated['name'],
            'org_code' => $validated['org_code'],
            'logo' => $validated['logo'] ?? null,
        ]);

        // Create the admin user
        $organization->users()->create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
        ]);

        return redirect()->route('university_org.offices.index', $organization)->with('success', 'Organization and admin account created successfully.');
    }
}