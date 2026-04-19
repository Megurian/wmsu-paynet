<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\EmployeeAssignment;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'suffix' => 'nullable|string|max:10',
            'department' => 'nullable|string',
            'email' => 'required|email|unique:employees,email',
            'position' => 'nullable|array',
            'position.*' => 'in:assessor,student_coordinator,adviser,treasurer',
        ]);

        $department = $request->department;

        if ($request->department === 'other') {
            $department = $request->other_department;
        }

        Employee::create([
            'college_id' => Auth::user()->college_id,
           'first_name' => strtoupper($request->first_name),
            'last_name' => strtoupper($request->last_name),
            'middle_name' => strtoupper($request->middle_name),
            'suffix' => $request->suffix,
             'email' => $request->email, 
            'department' => $department,
            'position' => $request->position ?? [],
            'has_account' => false,
        ]);

        return back()->with('status', 'Employee added successfully');
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'suffix' => 'nullable|string|max:10',
            'email' => 'required|email',
            'department' => 'nullable|string',
        ]);

        $department = $request->department;

        if ($request->department === 'other') {
            $department = $request->other_department;
        }

        $employee->update([
            'first_name' => strtoupper($request->first_name),
            'last_name' => strtoupper($request->last_name),
            'middle_name' => strtoupper($request->middle_name),
            'suffix' => $request->suffix,
            'email' => $request->email,
            'department' => $department,
        ]);

        return back()->with('status', 'Employee updated successfully!');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->back()->with('status', 'Employee deleted!');
    }

public function createAccount(Request $request, Employee $employee)
{
    $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'position' => 'required|array',
        'position.*' => 'in:assessor,student_coordinator,adviser,treasurer',
        'course_id' => 'nullable|exists:courses,id',
    ]);

    $roles = $request->position;

    if (in_array('adviser', $roles) && !$request->course_id) {
        return back()->withErrors([
            'course_id' => 'Course is required when assigning Adviser role.'
        ]);
    }

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    EmployeeAssignment::updateOrCreate(
        [
            'employee_id' => $employee->id,
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
        ],
        [
            'positions' => $roles,
            'course_id' => in_array('adviser', $roles) ? $request->course_id : null,
        ]
    );

    $user = User::create([
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'first_name' => $employee->first_name,
        'last_name' => $employee->last_name,
        'college_id' => auth()->user()->college_id,
        'role' => $roles,
        'course_id' => in_array('adviser', $roles) ? $request->course_id : null,
    ]);

    $employee->update([
        'has_account' => true,
        'user_id' => $user->id,
        'email' => $employee->email ?? $request->email, 
    ]);

    return back()->with('status', 'Account created successfully');
}

public function bulkAssign(Request $request)
{
    $rolesData = $request->roles ?? [];
    $courseData = $request->course_id ?? [];

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    foreach ($rolesData as $employeeId => $roles) {

        $employee = Employee::find($employeeId);
        if (!$employee) continue;

        $roles = array_values($roles);

        $existing = $employee->currentAssignment?->positions ?? [];
        $existingCourse = $employee->currentAssignment?->course_id;

        if (in_array('adviser', $existing) && !in_array('adviser', $roles)) {
            $roles[] = 'adviser';
        }

        $roles = array_values(array_unique($roles));

        $courseId = $courseData[$employeeId] ?? $existingCourse;

        if (!in_array('adviser', $roles)) {
            $courseId = null;
        }

        EmployeeAssignment::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'school_year_id' => $activeSY->id,
                'semester_id' => $activeSem->id,
            ],
            [
                'positions' => $roles,
                'course_id' => $courseId,
            ]
        );

        if ($employee->user_id) {
            $user = User::find($employee->user_id);

            if ($user) {
                $user->update([
                    'role' => $roles,
                    'course_id' => $courseId,
                ]);
            }
        }
    }

    return back()->with('status', 'Role assignments synced successfully!');
}

public function toggle(Employee $employee)
{
    $employee->update([
        'is_active' => !$employee->is_active
    ]);

    return back()->with('status', 'Employee status updated!');
}


}
