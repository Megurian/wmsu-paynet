<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fee;

class LocalOrgsController extends Controller
{
    public function index()
    {
        $collegeId = Auth::user()->college_id;
        $orgs = Organization::where('college_id', $collegeId)->get();

        return view('college.local_organizations.college_org', compact('orgs'));
    }

    public function create()
    {
        return view('college.local_organizations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'org_code' => 'required|string|unique:organizations,org_code',
            'logo' => 'nullable|image|max:2048',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($request) {
            $logoPath = $request->hasFile('logo')
                ? $request->file('logo')->store('org_logos', 'public')
                : null;

            $org = Organization::create([
                'name' => $request->name,
                'org_code' => $request->org_code,
                'role' => 'college_org',
                'college_id' => Auth::user()->college_id,
                'mother_organization_id' => null,
                'status' => 'pending',
                'logo' => $logoPath,
            ]);

            User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'college_org',
                'college_id' => Auth::user()->college_id,
                'organization_id' => $org->id,
            ]);
        });

        return redirect()->route('college.local_organizations')
            ->with('success', 'Organization submitted for dean approval and initial admin created.');
    }

    public function show(Organization $org)
    {
        // Load approved fees for this organization
        $fees = Fee::where('organization_id', $org->id)
                    ->where('status', 'approved')
                    ->get();

        // Load users under this organization
        $users = User::where('organization_id', $org->id)->get();

        return view('college.local_organizations.show', compact('org', 'fees', 'users'));
    }
}
