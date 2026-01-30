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

class AdviserStudentUploadController extends Controller
{
    public function index()
    {
        $adviserId = Auth::id();
        $collegeId = Auth::user()->college_id;

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        // Adviser students for active SY/Sem
       $students = StudentEnrollment::with('student', 'course', 'yearLevel', 'section')
        ->where('adviser_id', $adviserId)
        ->where('status', 'FOR_PAYMENT_VALIDATION') // optional if you want only new students
        ->when($activeSY, fn($q) => $q->where('school_year_id', $activeSY->id))
        ->when($activeSem, fn($q) => $q->where('semester_id', $activeSem->id))
        ->orderBy('id', 'desc')
        ->get();

        // Load courses, years, sections for dropdowns
        $courses = Course::where('college_id', $collegeId)->get();
        $years = YearLevel::where('college_id', $collegeId)->get();
        $sections = Section::where('college_id', $collegeId)->get();

        return view('college.students.my-upload', compact('students', 'courses', 'years', 'sections'));
    }

    public function store(Request $request)
    {
        $adviserId = Auth::id();
        $collegeId = Auth::user()->college_id;

        $request->validate([
            'student_id' => 'required|string|max:50|unique:students,student_id,NULL,id,college_id,' . $collegeId,
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $student = Student::create([
            ...$request->all(),
            'college_id' => $collegeId,
        ]);

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if ($activeSY && $activeSem) {
            StudentEnrollment::create([
                'student_id' => $student->id,
                'college_id' => $collegeId,
                'course_id' => $request->course_id,
                'year_level_id' => $request->year_level_id,
                'section_id' => $request->section_id,
                'school_year_id' => $activeSY->id,
                'semester_id' => $activeSem->id,
                'adviser_id' => $adviserId,
                'status' => 'FOR_PAYMENT_VALIDATION',
            ]);
        }

        return back()->with('status', 'Student uploaded successfully.');
    }

    public function reAddOldStudent($studentId)
    {
        $adviserId = Auth::id();
        $collegeId = Auth::user()->college_id;

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        StudentEnrollment::updateOrCreate(
            [
                'student_id' => $studentId,
                'school_year_id' => $activeSY->id,
                'semester_id' => $activeSem->id,
            ],
            [
                'college_id' => $collegeId,
                'adviser_id' => $adviserId,
                'status' => 'FOR_PAYMENT_VALIDATION',
            ]
        );

        return back()->with('status', 'Student re-added successfully.');
    }
}
