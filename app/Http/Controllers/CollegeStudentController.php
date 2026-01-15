<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
use Illuminate\Validation\Rule;
class CollegeStudentController extends Controller
{
    public function index()
    {
        $collegeId = Auth::user()->college_id;

        return view('college.students', [
            'students' => Student::where('college_id', $collegeId)
            ->with(['course','yearLevel','section'])
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'student_id' => $s->student_id,
                'last_name' => $s->last_name,
                'first_name' => $s->first_name,
                'middle_name' => $s->middle_name,
                'suffix' => $s->suffix,
                'course' => $s->course?->name,
                'course_id' => $s->course_id,
                'year' => $s->yearLevel?->name,
                'year_level_id' => $s->year_level_id,
                'section' => $s->section?->name,
                'section_id' => $s->section_id,
                'contact' => $s->contact,
                'email' => $s->email,
            ]),

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

        Student::create([
            ...$request->all(),
            'college_id' => $collegeId,
        ]);

        return back()->with('success', 'Student added successfully.');
    }

    public function destroy($id)
    {
        Student::findOrFail($id)->delete();
        return back()->with('success', 'Student removed successfully.');
    }
}
