<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'department' => 'nullable|string',
            'position' => 'nullable|in:faculty,staff',
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
            'position' => $request->position,
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
}
