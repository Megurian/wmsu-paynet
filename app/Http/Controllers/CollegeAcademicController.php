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
        $collegeId = Auth::user()->college_id;

        return view('college.academics', [
            'courses' => Course::where('college_id', $collegeId)->get(),
            'years' => YearLevel::where('college_id', $collegeId)->get(),
            'sections' => Section::where('college_id', $collegeId)->get(),
        ]);
    }

    public function storeCourse(Request $request)
    {
        Course::create([
            'college_id' => Auth::user()->college_id,
            'name' => $request->name,
        ]);
       return back()->with('status', 'Course added successfully.');
    }

    public function storeYear(Request $request)
    {
        YearLevel::create([
            'college_id' => Auth::user()->college_id,
            'name' => $request->name,
        ]);

       return back()->with('status', 'Year Level added successfully.');
    }

    public function storeSection(Request $request)
    {
        Section::create([
            'college_id' => Auth::user()->college_id,
            'name' => $request->name,
        ]);

        return back()->with('status', 'Section added successfully.');
    }

    public function destroyCourse($id)
    {
        Course::findOrFail($id)->delete();
       return back()->with('status', 'Course removed successfully.');
    }

    public function destroyYear($id)
    {
        YearLevel::findOrFail($id)->delete();
        return back()->with('status', 'Year Level removed successfully.');
    }

    public function destroySection($id)
    {
        Section::findOrFail($id)->delete();
        return back()->with('status', 'Section removed successfully.');
    }

}
