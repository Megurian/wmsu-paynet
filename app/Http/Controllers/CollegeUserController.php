<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CollegeUserController extends Controller
{
    public function index()
    {
        $collegeId = Auth::user()->college_id;
        $users = User::where('college_id', $collegeId)
                     ->whereIn('role', ['student_coordinator', 'adviser', 'assessor'])
                     ->get();

        return view('college.users', compact('users'));
    }

    public function create()
    {
        return view('college.create-user');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:student_coordinator,adviser,assessor',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'college_id' => Auth::user()->college_id,
        ]);

        return redirect()->route('college.users.index')
                         ->with('status', 'User created successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->college_id !== Auth::user()->college_id || !in_array($user->role, ['student_coordinator', 'adviser', 'assessor'])) {
            abort(403, 'Unauthorized access.');
        }

        $user->delete();

        return redirect()->route('college.users.index')
                        ->with('status', 'User deleted successfully!');
    }

}
