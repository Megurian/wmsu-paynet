<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;

class ValidateStudentsController extends Controller
{


    public function index()
    {
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        // students not yet validated for this semester
        $students = Student::where('college_id', $collegeId)
            ->whereDoesntHave('enrollments', function ($q) use ($activeSY, $activeSem) {
                $q->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id);
            })
            ->get();

        // fetch all courses, years, sections for dropdowns
        $courses = Course::where('college_id', $collegeId)->get();
        $years = YearLevel::where('college_id', $collegeId)->get();
        $sections = Section::where('college_id', $collegeId)->get();

        return view('college.validate_students', compact('students', 'activeSY', 'activeSem', 'courses', 'years', 'sections'));
    }


    public function store(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        StudentEnrollment::create([
            'student_id' => $student->id,
            'college_id' => $collegeId,
            'course_id' => $request->course_id,
            'year_level_id' => $request->year_level_id,
            'section_id' => $request->section_id,
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return back()->with('success', 'Student validated successfully.');
    }
}

