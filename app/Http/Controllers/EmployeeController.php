<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'department' => 'nullable|string',
            'position' => 'nullable|array',
            'position.*' => 'in:assessor,student_coordinator,adviser,treasurer',
        ]);

        $department = $request->department;

        if ($request->department === 'other') {
            $department = $request->other_department;
        }

        Employee::create([
            'college_id' => Auth::user()->college_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'department' => $department,
            'position' => $request->position ?? [],
            'has_account' => false,
        ]);

        return back()->with('status', 'Employee added successfully');
    }

    public function update(Request $request, Employee $employee)
    {
        $employee->update($request->all());

        return redirect()->back()->with('status', 'Employee updated!');
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
    ]);

    // ✅ GET ROLES FROM FORM (NOT FROM EMPLOYEE)
    $roles = $request->position;

    // ✅ SAVE ROLES BACK TO EMPLOYEE
    $employee->update([
        'position' => $roles,
    ]);

    // ✅ PRIORITY LOGIC
    $priority = ['adviser', 'assessor', 'treasurer', 'student_coordinator'];

    $primaryRole = 'student_coordinator';

    foreach ($priority as $role) {
        if (in_array($role, $roles)) {
            $primaryRole = $role;
            break;
        }
    }

    // ✅ CREATE USER
    $user = User::create([
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'first_name' => $employee->first_name,
        'last_name' => $employee->last_name,
        'college_id' => auth()->user()->college_id,
         'role' => $roles,
    ]);

    // ✅ LINK ACCOUNT
    $employee->update([
        'has_account' => true,
        'user_id' => $user->id,
    ]);

    return back()->with('status', 'Account created successfully');
}
}
