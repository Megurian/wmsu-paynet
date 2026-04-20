<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;

class CollegeAcademicController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $collegeId = $user->college_id;

        return view('college.academics', [
            'courses' => Course::where('college_id', $collegeId)->get(),
            'years' => YearLevel::where('college_id', $collegeId)->get(),
            'sections' => Section::where('college_id', $collegeId)->get(),
        ]);
    }

    public function storeCourse(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Course::create([
            'college_id' => $user->college_id,
            'name' => $request->name,
        ]);

        return back()->with('status', 'Course added successfully.');
    }

    public function storeYear(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        YearLevel::create([
            'college_id' => $user->college_id,
            'name' => $request->name,
        ]);

        return back()->with('status', 'Year Level added successfully.');
    }

    public function storeSection(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Section::create([
            'college_id' => $user->college_id,
            'name' => $request->name,
        ]);

        return back()->with('status', 'Section added successfully.');
    }

    public function destroyCourse($id)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        Course::findOrFail($id)->delete();
        return back()->with('status', 'Course removed successfully.');
    }

    public function destroyYear($id)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        YearLevel::findOrFail($id)->delete();
        return back()->with('status', 'Year Level removed successfully.');
    }

    public function destroySection($id)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        Section::findOrFail($id)->delete();
        return back()->with('status', 'Section removed successfully.');
    }

}
