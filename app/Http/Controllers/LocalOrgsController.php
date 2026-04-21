<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fee;

use App\Models\Payment;

class LocalOrgsController extends Controller
{
    public function index()
    {
        $collegeId = Auth::user()->college_id;

        $orgs = Organization::with('users')
            ->where('college_id', $collegeId)
            ->get();

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

            $activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
            $activeSem = \App\Models\Semester::where('is_active', true)->first();

            $org = Organization::create([
                'name' => $request->name,
                'org_code' => $request->org_code,
                'role' => 'college_org',
                'college_id' => Auth::user()->college_id,
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
                'college_id' => Auth::user()->college_id,
                'organization_id' => $org->id,
            ]);
        });

        return redirect()->route('college.local_organizations')
            ->with('success', 'Organization submitted for dean approval and initial admin created.');
    }

    public function show(Organization $org)
    {
        
        $fees = Fee::where('organization_id', $org->id)
            ->where('status', 'approved')
            ->get();

        // Get current officers from the pivot/record table
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

    public function assignOfficer(Request $request, Organization $org)
{
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'role' => 'required|string',

        'secondary_email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $student = \App\Models\Student::findOrFail($request->student_id);

    // جلوگیری duplicate role
    $exists = DB::table('organization_officers')
        ->where('student_id', $student->id)
        ->where('organization_id', $org->id)
        ->where('role', $request->role)
        ->exists();

    if ($exists) {
        return back()->with('error', 'This student already has this role.');
    }

    DB::transaction(function () use ($student, $org, $request) {

        $user = User::create([
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $request->secondary_email,
            'password' => Hash::make($request->password),

            'role' => 'college_org',
            'organization_id' => $org->id,
            'college_id' => auth()->user()->college_id,
        ]);

        // ✅ UPDATE STUDENT
        $student->update([
            'organization_id' => $org->id,
            'is_officer' => true,
        ]);

        $activeSem = \App\Models\Semester::where('is_active', true)->first();

        // ✅ SAVE OFFICER RECORD
        DB::table('organization_officers')->insert([
            'student_id' => $student->id, // ✅ FIXED
            'organization_id' => $org->id,
            'role' => $request->role,
            'semester_id' => $activeSem?->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    return back()->with('success', 'Officer assigned with login credentials.');
}

    public function cancelSubmission(Organization $org)
    {
        if ($org->status === 'pending') {
            $org->delete();
            return redirect()->route('college.local_organizations')->with('status', 'Submission canceled successfully.');
        }
        return redirect()->back()->with('error', 'Cannot cancel this submission.');
    }


    public function records(Organization $org)
    {
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
