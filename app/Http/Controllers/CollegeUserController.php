<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
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

        return view('college.users', [
            'users' => $users,
            'courses' => Course::where('college_id', $collegeId)->get(),
            'years' => YearLevel::where('college_id', $collegeId)->get(),
            'sections' => Section::where('college_id', $collegeId)->get(),
        ]);
    }

    public function create()
    {
        return view('college.create-user');
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
            'role' => 'required|in:student_coordinator,adviser,assessor',
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

    public function updateCollegeLogo(Request $request)
    {
        $request->validate([
            'college_logo' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $college = Auth::user()->college;

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
        $college->name = $request->college_name;
        $college->save();

        return redirect()->route('college.users.index', ['tab' => 'college'])
                        ->with('status', 'College name updated successfully!');
    }

}
