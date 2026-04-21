<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fee;
use App\Models\OrganizationOfficer;
use Illuminate\Validation\ValidationException;

use App\Models\Payment;

class LocalOrgsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $collegeId = $user->college_id;

        $orgs = Organization::with('users')
            ->where('college_id', $collegeId)
            ->get();

        return view('college.local_organizations.college_org', compact('orgs'));
    }

    public function create()
    {
        abort_unless(Auth::user(), 403);
        return view('college.local_organizations.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

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

        DB::transaction(function () use ($request, $user) {
            $logoPath = $request->hasFile('logo')
                ? $request->file('logo')->store('org_logos', 'public')
                : null;

            $activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
            $activeSem = \App\Models\Semester::where('is_active', true)->first();

            $org = Organization::create([
                'name' => $request->name,
                'org_code' => $request->org_code,
                'role' => 'college_org',
                'college_id' => $user->college_id,
                'mother_organization_id' => null,
                'status' => 'pending',
                'logo' => $logoPath,
                'created_school_year_id' => $activeSY?->id,
                'created_semester_id' => $activeSem?->id,
            ]);

            User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'college_org',
                'college_id' => $user->college_id,
                'organization_id' => $org->id,
            ]);
        });

        return redirect()->route('college.local_organizations')
            ->with('success', 'Organization submitted for dean approval and initial admin created.');
    }

    public function show(Organization $org)
    {

        $user = Auth::user();
        abort_unless($user, 403);
        abort_unless($org->college_id === $user->college_id, 403);
            $fees = Fee::where('organization_id', $org->id)
                ->where('status', 'approved')
                ->get();

        $officers = DB::table('organization_officers')
            ->join('students', 'students.id', '=', 'organization_officers.student_id')
            ->where('organization_officers.organization_id', $org->id)
            ->where('organization_officers.is_active', true)
            ->select(
                'students.id',
                'students.first_name',
                'students.last_name',
                'students.email',
                'students.student_id',
                'organization_officers.role',
                'organization_officers.is_active'
            )
            ->get();
        $activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
        $activeSem = \App\Models\Semester::where('is_active', true)->first();

        $eligibleStudents = \App\Models\Student::whereHas('enrollments', function ($q) use ($activeSY, $activeSem) {
            $q->where('college_id', Auth::user()->college_id)
                ->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id)
                ->where('status', 'ENROLLED');
        })
            ->select('id', 'student_id', 'last_name', 'first_name', 'email')
            ->get();

        return view('college.local_organizations.show', compact('org', 'fees', 'officers', 'eligibleStudents'));
    }


public function assignOfficer(Request $request, $orgId)
{
    $user = Auth::user();
    abort_unless($user, 403);
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'role' => 'required|string',
    ]);

    $activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
$activeSem = \App\Models\Semester::where('is_active', true)->first();
    $exists = OrganizationOfficer::where('organization_id', $orgId)
        ->where('student_id', $request->student_id)
        ->exists();

    if ($exists) {
        throw ValidationException::withMessages([
            'student_id' => 'This student is already assigned as an officer in this organization.',
        ]);
    }

    $roleTaken = OrganizationOfficer::where('organization_id', $orgId)
        ->where('student_id', $request->student_id)
        ->whereNotNull('role')
        ->exists();

    if ($roleTaken) {
        throw ValidationException::withMessages([
            'student_id' => 'A student can only hold one officer role per organization.',
        ]);
    }

    OrganizationOfficer::create([
        'organization_id' => $orgId,
        'student_id' => $request->student_id,
        'role' => $request->role,
        'secondary_email' => $request->secondary_email,
        'password' => bcrypt($request->password),
        'is_active' => true,
         'school_year_id' => $activeSY?->id,
    'semester_id' => $activeSem?->id,
    ]);

    return back()->with('success', 'Officer assigned successfully.');
}

    public function cancelSubmission(Organization $org)
    {
        $user = Auth::user();
        abort_unless($user, 403);
        abort_unless($org->college_id === $user->college_id, 403);

        if ($org->status === 'pending') {
            $org->delete();
            return redirect()->route('college.local_organizations')->with('status', 'Submission canceled successfully.');
        }
        return redirect()->back()->with('error', 'Cannot cancel this submission.');
    }


    public function records(Organization $org)
    {
        $user = Auth::user();
        abort_unless($user, 403);
        abort_unless($org->college_id === $user->college_id, 403);

        $payments = Payment::with([
            'student',
            'enrollment.course',
            'enrollment.yearLevel',
            'enrollment.section',
            'fees'
        ])
            ->whereHas('fees', function ($query) use ($org) {
                $query->where('organization_id', $org->id);
            })
            ->latest()
            ->get();

        return view('college.local_organizations.college_org.records', compact('payments', 'org'));
    }
}
