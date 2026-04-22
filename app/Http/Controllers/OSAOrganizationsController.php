<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\College;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Notifications\CollegeAdminInvitationNotification;

class OSAOrganizationsController extends Controller
{
    public function index()
    {
        $organizations = Organization::with('college', 'orgAdmin')->get();
        return view('osa.organizations', compact('organizations'));
    }

    public function create()
    {
        $colleges = College::all();
        return view('osa.create-organization', compact('colleges'));
    }

    /**
     * AJAX: Check org_code uniqueness
     */
    public function checkCode(Request $request)
    {
        $code = strtoupper(trim($request->input('org_code', '')));
        $available = !Organization::whereRaw('upper(org_code) = ?', [$code])->exists();
        return response()->json(['available' => $available]);
    }

    /**
     * AJAX: Check admin email uniqueness
     */
    public function checkEmail(Request $request)
    {
        $email = trim($request->input('admin_email', ''));
        $available = !User::where('email', $email)->exists();
        return response()->json(['available' => $available]);
    }

    public function resendInvite(Organization $organization)
    {
        $admin = $organization->orgAdmin;

        if (! $admin || $admin->invitation_active) {
            return back()->with('status', 'No pending organization invite found.');
        }

        $token = Password::broker()->createToken($admin);
        $admin->update(['invitation_sent_at' => now()]);
        $admin->notify(new CollegeAdminInvitationNotification(
            $token,
            $organization->role === 'college_org' ? 'college president' : 'organization president',
            $organization->name
        ));

        return back()->with('status', 'Invitation link resent successfully.');
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
            'admin_last_name' => 'required|string|max:255',
            'admin_first_name' => 'required|string|max:255',
            'admin_middle_name' => 'nullable|string|max:255',
            'admin_suffix' => 'nullable|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $logoPath = $request->file('logo')?->store('organizations', 'public');

                $status = null;
                if ($request->role === 'college_org' && $request->college_id) {
                    $status = 'pending';
                }
                $activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
                $activeSem = \App\Models\Semester::where('is_active', true)->first();
                $organization = Organization::create([
                    'name' => $request->name,
                    'org_code' => strtoupper($request->org_code),
                    'role' => $request->role,
                    'college_id' => $request->college_id,
                    'logo' => $logoPath,
                    'status' => $status,
                    'mother_organization_id' => $request->mother_organization_id ?? null,
                    'created_school_year_id' => $activeSY?->id,
                    'created_semester_id' => $activeSem?->id,
                ]);

                $admin = User::create([
                    'last_name' => $request->admin_last_name,
                    'first_name' => $request->admin_first_name,
                    'middle_name' => $request->admin_middle_name,
                    'suffix' => $request->admin_suffix,
                    'email' => $request->admin_email,
                    'password' => Str::random(64),
                    'role' => $request->role,
                    'organization_id' => $organization->id,
                    'invitation_sent_at' => now(),
                ]);

                $token = Password::broker()->createToken($admin);
                $admin->notify(new CollegeAdminInvitationNotification(
                    $token,
                    $request->role === 'college_org' ? 'college president' : 'organization president',
                    $organization->name
                ));
            });

            return redirect()->route('osa.organizations')->with('status', 'Organization created and invitation email sent to the admin.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create organization: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $organization = Organization::with('college', 'orgAdmin')->find($id);

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

    /**
     * Toggle OSA fee inheritance for an organization
     */
    public function toggleOsaInheritance($id)
    {
        $organization = Organization::findOrFail($id);

        $organization->update([
            'inherits_osa_fees' => !$organization->inherits_osa_fees
        ]);

        $message = $organization->inherits_osa_fees
            ? 'Organization now inherits OSA fees.'
            : 'Organization no longer inherits OSA fees.';

        return redirect()->route('osa.organizations')->with('status', $message);
    }
}
