<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\OrganizationOfficer;


class CollegeOrgManagementController extends Controller
{

    public function index()
    {
        $organization = Auth::user()->organization;

        $officers = OrganizationOfficer::with([
            'student.latestEnrollment.course',
            'student.latestEnrollment.yearLevel',
            'student.latestEnrollment.section',
            'student',
             'user'
        ])
        ->where('organization_id', $organization->id)
        ->where('is_active', true)
        ->get();

        $orgAccounts = \App\Models\User::where('organization_id', $organization->id)
            ->where('role', 'college_org')
            ->pluck('email', 'organization_id');
        return view('college_org.organization_management.index', compact('organization', 'officers'));
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'organization_logo' => 'required|image|max:2048'
        ]);

        $organization = auth()->user()->organization;

        if ($request->hasFile('organization_logo')) {

            if ($organization->logo && Storage::disk('public')->exists($organization->logo)) {
                Storage::disk('public')->delete($organization->logo);
            }

            $path = $request->file('organization_logo')->store('organization_logos', 'public');

            $organization->update([
                'logo' => $path
            ]);
        }

        return back()->with('status', 'Organization logo updated successfully.');
    }

    public function updateName(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255'
        ]);

        $organization = auth()->user()->organization;

        $organization->update([
            'name' => $request->organization_name
        ]);

        return back()->with('status', 'Organization name updated successfully.');
    }
}