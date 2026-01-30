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
   public function index(Request $request)
    {
        $search = request('search');
        $courseId = request('course_id');
        $yearLevelId = request('year_level_id');
        $sectionId = request('section_id');
        $adviserId = Auth::id();
        $collegeId = Auth::user()->college_id;

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $students = Student::where('college_id', $collegeId)
            ->whereHas('enrollments', function ($q) use ($adviserId) {
                $q->where('adviser_id', $adviserId);
            })
            ->where(function ($q) use ($request) {

                if ($request->filled('search')) {
                    $q->where(function ($s) use ($request) {
                        $s->where('student_id', 'like', '%' . $request->search . '%')
                        ->orWhere('last_name', 'like', '%' . $request->search . '%')
                        ->orWhere('first_name', 'like', '%' . $request->search . '%');
                    });
                }

                if ($request->filled('course_id')) {
                    $q->whereHas('enrollments', function ($e) use ($request) {
                        $e->where('course_id', $request->course_id);
                    });
                }

                if ($request->filled('year_level_id')) {
                    $q->whereHas('enrollments', function ($e) use ($request) {
                        $e->where('year_level_id', $request->year_level_id);
                    });
                }

                if ($request->filled('section_id')) {
                    $q->whereHas('enrollments', function ($e) use ($request) {
                        $e->where('section_id', $request->section_id);
                    });
                }
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
            ->map(fn ($rows) => $rows->first());

        $alpineStudents = $students->map(function($s) use ($previousEnrollments) {
            $enrollment = $s->enrollments->first();
            $prev = $previousEnrollments[$s->id] ?? null;

            return [
                'id' => $s->id,
                'student_id' => $s->student_id,
                'last_name' => $s->last_name,
                'first_name' => $s->first_name,
                'middle_name' => $s->middle_name,
                'course_id' => $enrollment->course_id ?? $prev->course_id ?? null,
                'course' => $enrollment->course->name ?? $prev->course->name ?? null,
                'year_level_id' => $enrollment->year_level_id ?? $prev->year_level_id ?? null,
                'year_level' => $enrollment->yearLevel->name ?? $prev->yearLevel->name ?? null,
                'section_id' => $enrollment->section_id ?? $prev->section_id ?? null,
                'section' => $enrollment->section->name ?? $prev->section->name ?? null,
                'status' => $enrollment->status ?? 'NOT ENROLLED',
            ];
        });

        return view(
            'college.students.my-upload',
            compact('students', 'courses', 'years', 'sections', 'previousEnrollments', 'activeSY', 'activeSem', 'alpineStudents')
        );
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
