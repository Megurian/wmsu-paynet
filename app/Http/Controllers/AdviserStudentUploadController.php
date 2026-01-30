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

        $students = Student::where('college_id', $collegeId)
            ->whereHas('enrollments', function ($q) use ($adviserId) {
                $q->where('adviser_id', $adviserId);
            })
            ->with(['enrollments' => function ($q) use ($activeSY, $activeSem) {
                $q->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id);
            }])
            ->get();

        $courses = Course::where('college_id', $collegeId)->get();
        $years = YearLevel::where('college_id', $collegeId)->get();
        $sections = Section::where('college_id', $collegeId)->get();

        $previousEnrollments = StudentEnrollment::with(['course', 'yearLevel', 'section'])
            ->whereIn('student_id', $students->pluck('id'))
            ->where(function($q) use ($activeSY, $activeSem) {
                $q->where('school_year_id', '<', $activeSY->id)
                ->orWhere(function($q2) use ($activeSY, $activeSem) {
                    $q2->where('school_year_id', $activeSY->id)
                        ->where('semester_id', '<', $activeSem->id);
                });
            })
            ->orderBy('school_year_id', 'desc')
            ->orderBy('semester_id', 'desc')
            ->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->first());

        return view('college.students.my-upload', compact('students', 'courses', 'years', 'sections', 'previousEnrollments', 'activeSY', 'activeSem'));
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

public function reAddOldStudent(Request $request, $studentId)
{
    $adviserId = Auth::id();
    $collegeId = Auth::user()->college_id;

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $prev = StudentEnrollment::where('student_id', $studentId)
        ->where(function($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', '<', $activeSY->id)
              ->orWhere(function($q2) use ($activeSY, $activeSem) {
                  $q2->where('school_year_id', $activeSY->id)
                     ->where('semester_id', '<', $activeSem->id);
              });
        })
        ->latest('id')
        ->first();

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
            'course_id' => $request->course_id ?? $prev?->course_id,
            'year_level_id' => $request->year_level_id ?? $prev?->year_level_id,
            'section_id' => $request->section_id ?? $prev?->section_id,
        ]
    );

    return back()->with('status', 'Student re-added successfully.');
}



}
