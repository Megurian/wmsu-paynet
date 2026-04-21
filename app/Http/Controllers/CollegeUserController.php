<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class CollegeUserController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $collegeId = $user->college_id;

    
        $allEmployees = Employee::with(['user', 'currentAssignment'])
            ->where('college_id', $collegeId)
            ->get();

        $accountEmployees = Employee::with(['user', 'currentAssignment'])
        ->where('college_id', $collegeId)
        ->where('has_account', true)
        ->whereNotNull('user_id')
        ->get();

        return view('college.users', [
            'users' => User::where('college_id', $collegeId)
                ->whereIn('role', ['treasurer', 'student_coordinator', 'adviser', 'assessor'])
                ->get(),

            'courses' => Course::where('college_id', $collegeId)->get(),
            'years' => YearLevel::where('college_id', $collegeId)->get(),
            'sections' => Section::where('college_id', $collegeId)->get(),

            'employees' => $allEmployees,
            'accountEmployees' => $accountEmployees,

            'activeSY' => $activeSY,
            'activeSem' => $activeSem,
        ]);
    }
    public function create()
    {
        
        $collegeId = Auth::user()->college_id;
        return view('college.create-user', [
            'courses' => Course::where('college_id', $collegeId)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:treasurer,student_coordinator,adviser,assessor',
            'course_id' => 'required_if:role,adviser|nullable|exists:courses,id',
        ]);

        $fullName = $request->first_name . ' ' .
                    ($request->middle_name ? $request->middle_name . ' ' : '') .
                    $request->last_name .
                    ($request->suffix ? ', ' . $request->suffix : '');

        User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'suffix' => $request->suffix,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'college_id' => Auth::user()->college_id,
            'course_id' => $request->role === 'adviser' ? $request->course_id : null,
        ]);

        return redirect()->route('college.users.index', ['tab' => 'accounts'])
                        ->with('status', 'User created successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->college_id !== Auth::user()->college_id || !in_array($user->role, ['treasurer', 'student_coordinator', 'adviser', 'assessor'])) {
            abort(403, 'Unauthorized access.');
        }

        $user->delete();

        return redirect()->route('college.users.index')
                        ->with('status', 'User deleted successfully!');
    }

    public function updateCollegeLogo(Request $request)
    {
        $request->validate([
            'college_logo' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $college = Auth::user()->college;
        abort_unless($college, 404);

        if ($request->hasFile('college_logo')) {
            $path = $request->file('college_logo')->store('college_logos', 'public');
            $college->logo = $path;
            $college->save();
        }

        return redirect()->route('college.users.index', ['tab' => 'college'])
                        ->with('status', 'College logo updated successfully!');
    }

    public function updateCollegeName(Request $request)
    {
        $request->validate([
            'college_name' => 'required|string|max:255',
        ]);

        $college = Auth::user()->college;
        abort_unless($college, 404);

        $college->name = $request->college_name;
        $college->save();

        return redirect()->route('college.users.index', ['tab' => 'college'])
                        ->with('status', 'College name updated successfully!');
    }

    public function assignCourse(Request $request, User $user)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($user->college_id !== Auth::user()->college_id || $user->role !== 'adviser') {
            abort(403, 'Unauthorized access.');
        }

        $user->course_id = $request->course_id;
        $user->save();

        return redirect()->route('college.users.index', ['tab' => 'accounts'])
                        ->with('status', 'Course assigned to adviser successfully!');
    }

public function toggle(Employee $employee)
{
    $user = Auth::user();
    abort_unless($user, 403);
    abort_unless($employee->college_id === $user->college_id, 403);

    $employee->update([
        'is_active' => !$employee->is_active
    ]);

    return back()->with('status', 'Employee status updated!');
}

public function roleHistory(Request $request)
{
    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $query = \App\Models\EmployeeAssignment::with([
        'employee',
        'schoolYear',
        'semester'
    ]);

    $schoolYearId = $request->school_year_id ?? $activeSY?->id;
    $semesterId = $request->semester_id ?? $activeSem?->id;

    if ($schoolYearId) {
        $query->where('school_year_id', $schoolYearId);
    }

    if ($semesterId) {
        $query->where('semester_id', $semesterId);
    }

    $assignments = $query->get();

    $history = $assignments->groupBy('employee_id')->map(function ($items) {
        $first = $items->first();

        return (object)[
            'employee' => $first->employee,
            'schoolYear' => $first->schoolYear,
            'semester' => $first->semester,
            'roles' => $items->pluck('positions')
                ->flatten()
                ->unique()
                ->values()
        ];
    })->values();

    $schoolYears = SchoolYear::orderByDesc('id')->get();

    $semesters = collect([
        (object)['id' => '1st Semester', 'name' => '1st Semester'],
        (object)['id' => '2nd Semester', 'name' => '2nd Semester'],
        (object)['id' => 'Summer', 'name' => 'Summer'],
    ]);

    return view('college.roles.history', compact(
        'history',
        'schoolYears',
        'semesters',
        'activeSY',
        'activeSem'
    ));
}
   
}
