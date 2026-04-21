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
        ->where('status', 'ENROLLED')
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

    public function show(Student $student)
    {
        $collegeId = Auth::user()->college_id;

        abort_unless($student->enrollments()->where('college_id', $collegeId)->exists(), 403);

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $enrollment = StudentEnrollment::with(['course', 'yearLevel', 'section'])
            ->where('student_id', $student->id)
            ->where('college_id', $collegeId)
            ->when($activeSY, fn ($q) => $q->where('school_year_id', $activeSY->id))
            ->when($activeSem, fn ($q) => $q->where('semester_id', $activeSem->id))
            ->first();

        return view('college.student-details', compact(
            'student',
            'enrollment',
            'activeSY',
            'activeSem'
        ));
    }




    public function store(Request $request)
    {
        $collegeId = Auth::user()->college_id;

        $request->validate([
            'student_id' => 'required|string|max:50',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'section_id' => 'required|exists:sections,id',
            'contact' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'suffix' => 'nullable|string|max:255',
             'religion' => 'nullable|string|max:255',
        ]);

        // Use updateOrCreate to handle existing students (e.g. transfers)
        $student = Student::updateOrCreate(
            ['student_id' => $request->student_id],
            $request->only(['last_name', 'first_name', 'middle_name', 'suffix', 'contact', 'email', 'religion'])
        );

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if ($activeSY && $activeSem) {
            StudentEnrollment::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'school_year_id' => $activeSY->id,
                    'semester_id' => $activeSem->id,
                ],
                [
                    'college_id' => $collegeId,
                    'course_id' => $request->course_id,
                    'year_level_id' => $request->year_level_id,
                    'section_id' => $request->section_id,
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                    'status' => 'ENROLLED', // Assuming admin adding means they are enrolled
                ]
            );
        }

        return back()->with('status', 'Student added/updated successfully.');
    }

    public function destroy($id)
    {
        $collegeId = Auth::user()->college_id;
        $student = Student::findOrFail($id);

        // Only remove enrollment records that belong to THIS college.
        // This preserves the student's identity and history in other colleges.
        $student->enrollments()->where('college_id', $collegeId)->delete();

        // If the student has no remaining enrollments anywhere, clean up.
        if ($student->enrollments()->doesntExist()) {
            $student->delete();
        }

        return back()->with('status', 'Student removed successfully.');
    }

    public function unvalidate($studentId)
    {
        $collegeId = Auth::user()->college_id;

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (! $activeSY || ! $activeSem) {
            return back()->withErrors([
                'academic_period' => 'No active school year or semester. Contact OSA for confirmation before unvalidating students.'
            ]);
        }

        StudentEnrollment::where('student_id', $studentId)
            ->where('college_id', $collegeId)
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->delete();

        return back()->with('status', 'Student removed from current semester.');
    }

    public function update(Request $request, Student $student)
{
    $collegeId = Auth::user()->college_id;
    abort_unless($student->enrollments()->where('college_id', $collegeId)->exists(), 403);

    $request->validate([
        'field' => ['required', 'string', Rule::in(['name', 'email', 'contact', 'religion'])],
        'value' => ['required', 'string', 'max:255'],
    ]);

    switch ($request->field) {
        case 'name':
            // Assuming value contains full name in format "Last, First Middle Suffix"
            $parts = explode(',', $request->value);
            $student->last_name = trim($parts[0] ?? $student->last_name);
            $student->first_name = trim($parts[1] ?? $student->first_name);
            $student->middle_name = trim($parts[2] ?? $student->middle_name);
            $student->suffix = trim($parts[3] ?? $student->suffix);
            break;
        case 'email':
            $student->email = $request->value;
            break;
        case 'contact':
            $student->contact = $request->value;
            break;
        case 'religion':
            $student->religion = $request->value;
            break;
    }

    $student->save();

    return back()->with('status', $request->field . ' updated successfully.');
}


}
