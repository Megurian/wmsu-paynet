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


    public function index(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $previousEnrollmentScope = function ($q) use ($activeSY, $activeSem) {
            $q->where(function ($sub) use ($activeSY, $activeSem) {
                $sub->where('school_year_id', '<', $activeSY->id)
                    ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                        $q2->where('school_year_id', $activeSY->id)
                        ->where('semester_id', '<', $activeSem->id);
                    });
            });
        };

        $students = Student::where('college_id', $collegeId)
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('student_id', 'like', "%{$request->search}%")
                        ->orWhere('first_name', 'like', "%{$request->search}%")
                        ->orWhere('last_name', 'like', "%{$request->search}%");
                });
            })

            ->when($request->course, function ($q) use ($request, $activeSY, $activeSem) {
                $q->whereHas('enrollments', function ($e) use ($request, $activeSY, $activeSem) {
                    $e->where(function ($sub) use ($request) {
                        $sub->where('course_id', $request->course);
                    })
                    ->where(function ($sub2) use ($activeSY, $activeSem) {
                        $sub2->where('school_year_id', '<', $activeSY->id)
                            ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                                $q2->where('school_year_id', $activeSY->id)
                                    ->where('semester_id', '<', $activeSem->id);
                            });
                    })
                    ->whereIn('id', function ($sq) {
                        $sq->selectRaw('MAX(id)')
                        ->from('student_enrollments')
                        ->groupBy('student_id');
                    });
                });
            })
            ->when($request->year, function ($q) use ($request, $activeSY, $activeSem) {
                $q->whereHas('enrollments', function ($e) use ($request, $activeSY, $activeSem) {
                    $e->where(function ($sub) use ($request) {
                        $sub->where('year_level_id', $request->year);
                    })
                    ->where(function ($sub2) use ($activeSY, $activeSem) {
                        $sub2->where('school_year_id', '<', $activeSY->id)
                            ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                                $q2->where('school_year_id', $activeSY->id)
                                    ->where('semester_id', '<', $activeSem->id);
                            });
                    })
                    ->whereIn('id', function ($sq) {
                        $sq->selectRaw('MAX(id)')
                        ->from('student_enrollments')
                        ->groupBy('student_id');
                    });
                });
            })
            ->when($request->section, function ($q) use ($request, $activeSY, $activeSem) {
                $q->whereHas('enrollments', function ($e) use ($request, $activeSY, $activeSem) {
                    $e->where(function ($sub) use ($request) {
                        $sub->where('section_id', $request->section);
                    })
                    ->where(function ($sub2) use ($activeSY, $activeSem) {
                        $sub2->where('school_year_id', '<', $activeSY->id)
                            ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                                $q2->where('school_year_id', $activeSY->id)
                                    ->where('semester_id', '<', $activeSem->id);
                            });
                    })
                    ->whereIn('id', function ($sq) {
                        $sq->selectRaw('MAX(id)')
                        ->from('student_enrollments')
                        ->groupBy('student_id');
                    });
                });
            })

            ->with(['enrollments' => function ($q) use ($activeSY, $activeSem) {
                $q->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id);
            }])
            ->paginate(10)
            ->withQueryString();


        foreach ($students as $student) {
            $lastEnrollment = StudentEnrollment::where('student_id', $student->id)
                ->where(function($q) use ($activeSY, $activeSem) {
                    $q->where('school_year_id', '<', $activeSY->id)
                    ->orWhere(function($q2) use ($activeSY, $activeSem) {
                        $q2->where('school_year_id', $activeSY->id)
                            ->where('semester_id', '<', $activeSem->id);
                    });
                })
                ->latest('id') // get most recent previous enrollment
                ->first();

            $student->lastEnrollment = $lastEnrollment;
        }

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
            "course_id.$studentId" => 'required|exists:courses,id',
            "year_level_id.$studentId" => 'required|exists:year_levels,id',
            "section_id.$studentId" => 'required|exists:sections,id',
        ]);

        StudentEnrollment::create([
            'student_id' => $student->id,
            'college_id' => $collegeId,
            'course_id' => $request->course_id[$student->id],
            'year_level_id' => $request->year_level_id[$student->id],
            'section_id' => $request->section_id[$student->id],
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return back()->with('success', 'Student validated successfully.');
    }

   public function bulkValidate(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $request->validate([
            'selected_students' => 'required|array',
            'selected_students.*' => 'exists:students,id',
        ]);

        foreach ($request->selected_students as $studentId) {

            $request->validate([
                "course_id.$studentId" => 'required|exists:courses,id',
                "year_level_id.$studentId" => 'required|exists:year_levels,id',
                "section_id.$studentId" => 'required|exists:sections,id',
            ]);

            StudentEnrollment::updateOrCreate(
                [
                    'student_id'     => $studentId,
                    'college_id'     => $collegeId,
                    'school_year_id' => $activeSY->id,
                    'semester_id'    => $activeSem->id,
                ],
                [
                    'course_id'      => $request->course_id[$studentId],
                    'year_level_id'  => $request->year_level_id[$studentId],
                    'section_id'     => $request->section_id[$studentId],
                    'validated_by'   => Auth::id(),
                    'validated_at'   => now(),
                ]
            );
        }

        return back()->with(
            'success',
            count($request->selected_students) . ' students validated successfully.'
        );
    }


}

