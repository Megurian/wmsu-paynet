<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\College;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OSAOrganizationsController extends Controller
{
    public function index()
    {
        $organizations = Organization::with('college', 'admin')->get();
        return view('osa.organizations', compact('organizations'));
    }

    public function create()
    {
        $colleges = College::all();
        return view('osa.create-organization', compact('colleges'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:university_org,college_org',
            'name' => 'required|string|max:255',
            'org_code' => 'required|string|max:50|unique:organizations,org_code',
            'college_id' => [
                'nullable',
                'exists:colleges,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->role === 'college_org' && !$value) {
                        $fail('College is required for college-based organizations.');
                    }
                },
            ],
            'logo' => 'nullable|image|max:2048',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $logoPath = $request->file('logo')?->store('organizations', 'public');

                $organization = Organization::create([
                    'name' => $request->name,
                    'org_code' => strtoupper($request->org_code),
                    'role' => $request->role,
                    'college_id' => $request->college_id,
                    'logo' => $logoPath,
                ]);

                User::create([
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                    'role' => $request->role,
                    'organization_id' => $organization->id,
                ]);
            });

            return redirect()->route('osa.organizations')->with('status', 'Organization and admin created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create organization: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $organization = Organization::with('college', 'admin')->find($id);

        if (!$organization) {
            return redirect()->route('osa.organizations')
                            ->withErrors('Organization not found.');
        }

        return view('osa.organization-details', ['orgDetail' => $organization]);

    }


    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);

        $organization->admin?->delete();

        $organization->delete();

        return redirect()->route('osa.organizations')->with('status', 'Organization deleted successfully!');
    }

}
