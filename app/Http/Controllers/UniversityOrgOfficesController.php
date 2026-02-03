<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;

class UniversityOrgOfficesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $organization = $user?->organization;

        if ($organization) {
            // Only show organizations for which the current organization is the mother
            $organizations = Organization::with(['college', 'motherOrganization'])->where('mother_organization_id', $organization->id)->orderBy('name')->get();
        } else {
            // Fallback: empty collection if user has no organization
            $organizations = collect();
        }

        return view('university_org.offices', compact('organizations', 'organization'));
    }

    public function create()
    {
        $colleges = \App\Models\College::orderBy('name')->get();
        return view('university_org.create-offices', compact('colleges'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string',
            'college_code' => 'required|string|exists:colleges,college_code',
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

        // Determine college id from code
        $college = \App\Models\College::where('college_code', $validated['college_code'])->first();

        // If the authenticated user belongs to a university-level organization, use it as the mother org
        $motherOrgId = null;
        if ($request->user() && $request->user()->organization_id) {
            $possibleMother = Organization::find($request->user()->organization_id);
            if ($possibleMother && $possibleMother->role === 'university_org') {
                $motherOrgId = $possibleMother->id;
            }
        }

        // Create the organization, recording the mother_organization_id when applicable
        $organization = Organization::create([
            'role' => $validated['role'],
            'name' => $validated['name'],
            'org_code' => $validated['org_code'],
            'logo' => $validated['logo'] ?? null,
            'college_id' => $college?->id,
            'mother_organization_id' => $motherOrgId,
        ]);

        // Create the admin user (assign to college_org role)
        $organization->users()->create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'role' => 'college_org',
            'college_id' => $college?->id,
            'organization_id' => $organization->id,
        ]);

        return redirect()->route('university_org.offices.index', $organization)->with('success', 'Organization and admin account created successfully.');
    }

    public function checkCode(Request $request)
    {
        $code = strtoupper($request->input('org_code', ''));
        $exists = Organization::whereRaw('UPPER(org_code) = ?', [$code])->exists();
        return response()->json(['available' => !$exists]);
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('admin_email', '');
        $exists = \App\Models\User::where('email', $email)->exists();
        return response()->json(['available' => !$exists]);
    }
}