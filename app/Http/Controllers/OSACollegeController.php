<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\College;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;

class OSACollegeController extends Controller
{
    public function index()
    {
        $colleges = College::all();
        return view('osa.college', compact('colleges'));
    }

    public function create()
    {
        return view('osa.create-college');
    }

    public function show($id)
    {
        $college = College::with([
            'admins' => function ($query) {$query->where('role', 'college');},
            'courses' 
        ])->findOrFail($id);

        $organizations = Organization::where('college_id', $college->id)
            ->with('admin')
            ->get();

        return view('osa.college-details', compact('college', 'organizations'));
    }




    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'college_code' => 'required|string|max:20|unique:colleges,college_code',
            'logo' => 'nullable|image|max:2048',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::transaction(function () use ($request) {

            $logoPath = $request->file('logo')
                ? $request->file('logo')->store('colleges', 'public')
                : null;

            $college = College::create([
                'name' => $request->name,
                'college_code' => strtoupper($request->college_code),
                'logo' => $logoPath,
            ]);

            User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'college',
                'college_id' => $college->id,
            ]);

            if ($request->has('courses')) {
                foreach ($request->courses as $course) {
                    if (!trim($course)) continue;
                    Course::create([
                        'college_id' => $college->id,
                        'name' => trim($course),
                    ]);
                }
            }

            if ($request->has('years')) {
                foreach ($request->years as $year) {
                    if (!trim($year)) continue;
                    YearLevel::create([
                        'college_id' => $college->id,
                        'name' => trim($year),
                    ]);
                }
            }

            if ($request->has('sections')) {
                foreach ($request->sections as $section) {
                    if (!trim($section)) continue;
                    Section::create([
                        'college_id' => $college->id,
                        'name' => trim($section),
                    ]);
                }
            }

        });

            return redirect()->route('osa.college')
                ->with('status', 'College and initial admin created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create college. Please try again.');
        }
    }


    public function destroy($id)
    {
        $college = College::findOrFail($id);

        
        $college->users()->delete();
        $college->delete();

        return redirect()->route('osa.college')->with('status', 'College deleted successfully!');
    }

    /**
     * AJAX: Check if a college code is available.
     */
    public function checkCode(Request $request)
    {
        $request->validate([
            'college_code' => 'required|string|max:20',
        ]);

        $code = strtoupper($request->input('college_code'));
        $exists = College::where('college_code', $code)->exists();

        return response()->json(['available' => !$exists]);
    }

    /**
     * AJAX: Check if an admin email is available.
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'admin_email' => 'required|email|max:255',
        ]);

        $exists = User::where('email', $request->input('admin_email'))->exists();

        return response()->json(['available' => !$exists]);
    }

}
