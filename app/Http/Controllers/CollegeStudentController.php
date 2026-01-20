<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
use Illuminate\Validation\Rule;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\StudentEnrollment;

class CollegeStudentController extends Controller
{
    public function index()
    {
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return view('college.students', [
                'students' => [],
                'courses' => Course::where('college_id', $collegeId)->get(),
                'years' => YearLevel::where('college_id', $collegeId)->get(),
                'sections' => Section::where('college_id', $collegeId)->get(),
                'message' => 'No active school year or semester. Please wait for validation.'
            ]);
        }

       $students = StudentEnrollment::with(['student', 'course', 'yearLevel', 'section'])
        ->join('students', 'student_enrollments.student_id', '=', 'students.id')
        ->where('student_enrollments.college_id', $collegeId)
        ->where('student_enrollments.school_year_id', $activeSY->id)
        ->where('student_enrollments.semester_id', $activeSem->id)
        ->orderBy('students.last_name')
        ->orderBy('students.first_name')
        ->select('student_enrollments.*')
        ->get()
        ->map(fn($s) => [
            'id' => $s->student->id,
            'student_id' => $s->student->student_id,
            'last_name' => $s->student->last_name,
            'first_name' => $s->student->first_name,
            'middle_name' => $s->student->middle_name,
            'suffix' => $s->student->suffix,
            'course' => $s->course?->name,
            'course_id' => $s->course_id,
            'year' => $s->yearLevel?->name,
            'year_level_id' => $s->year_level_id,
            'section' => $s->section?->name,
            'section_id' => $s->section_id,
            'contact' => $s->student->contact,
            'email' => $s->student->email,
        ]);

        return view('college.students', [
            'students' => $students,
            'courses' => Course::where('college_id', $collegeId)->get(),
            'years' => YearLevel::where('college_id', $collegeId)->get(),
            'sections' => Section::where('college_id', $collegeId)->get(),
        ]);
    }


    public function store(Request $request)
    {
        $collegeId = Auth::user()->college_id;

        $request->validate([
            'student_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students')->where(fn ($q) =>
                    $q->where('college_id', $collegeId)
                ),
            ],
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'section_id' => 'required|exists:sections,id',
            'contact' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'suffix' => 'nullable|string|max:255',
        ], [
            'student_id.unique' => 'This Student ID already exists in your college.',
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
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);
        }

        return back()->with('status', 'Student added successfully.');
    }

    public function destroy($id)
    {
        Student::findOrFail($id)->delete();
        return back()->with('status', 'Student removed successfully.');
    }

    public function unvalidate($studentId)
    {
        $collegeId = Auth::user()->college_id;

        $activeSY = SchoolYear::where('is_active', true)->firstOrFail();
        $activeSem = Semester::where('is_active', true)->firstOrFail();

        StudentEnrollment::where('student_id', $studentId)
            ->where('college_id', $collegeId)
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->delete();

        return back()->with('status', 'Student removed from current semester.');
    }






    
}
