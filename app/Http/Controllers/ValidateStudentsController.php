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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateExport;
use App\Imports\StudentsImport;

class ValidateStudentsController extends Controller
{


    public function index(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $latestEnrollmentSub = \DB::table('student_enrollments')
            ->selectRaw('MAX(id) as latest_id')
            ->groupBy('student_id');

        $studentsQuery = Student::where('college_id', $collegeId)
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('student_id', 'like', "%{$request->search}%")
                        ->orWhere('first_name', 'like', "%{$request->search}%")
                        ->orWhere('last_name', 'like', "%{$request->search}%");
                });
            })
            ->with(['enrollments' => function ($q) {
                $q->orderBy('school_year_id', 'desc')
                ->orderBy('semester_id', 'desc')
                ->orderBy('id', 'desc')
                ->limit(1);
            }]);

        $studentsQuery->whereHas('enrollments', function ($e) use ($request) {
            $e->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                ->from('student_enrollments')
                ->groupBy('student_id');
            });

            if ($request->course) {
                $e->where('course_id', $request->course);
            }
            if ($request->year) {
                $e->where('year_level_id', $request->year);
            }
            if ($request->section) {
                $e->where('section_id', $request->section);
            }
        });

        $students = $studentsQuery->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(40)
            ->withQueryString();

        $activeEnrollments = StudentEnrollment::where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->get()
            ->keyBy('student_id');

        $previousEnrollments = StudentEnrollment::whereIn('student_id', $students->pluck('id'))
            ->where(function ($q) use ($activeSY, $activeSem) {
                $q->where('school_year_id', '<', $activeSY->id)
                ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                    $q2->where('school_year_id', $activeSY->id)
                        ->where('semester_id', '<', $activeSem->id);
                });
            })
            ->orderBy('school_year_id', 'desc')
            ->orderBy('semester_id', 'desc')
            ->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->first());

        foreach ($students as $student) {
            $latest = $student->enrollments->first() ?? null;
            $student->displayEnrollment = $latest ?? $previousEnrollments[$student->id] ?? null;
        }

        $courses = Course::where('college_id', $collegeId)->get();
        $years = YearLevel::where('college_id', $collegeId)->get();
        $sections = Section::where('college_id', $collegeId)->get();

        return view('college.validate_students', compact(
            'students', 'activeSY', 'activeSem', 'courses', 'years', 'sections', 'activeEnrollments', 'previousEnrollments'
        ));
    }





    public function store(Request $request, $studentId)
    {
        abort_unless(Auth::user()->isAssessor(), 403);
        $student = Student::findOrFail($studentId);
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $request->validate([
            "course_id.$studentId" => 'required|exists:courses,id',
            "year_level_id.$studentId" => 'required|exists:year_levels,id',
            "section_id.$studentId" => 'required|exists:sections,id',
        ]);

        $enrollment = StudentEnrollment::updateOrCreate(
            [
                'student_id'     => $student->id,
                'school_year_id' => $activeSY->id,
                'semester_id'    => $activeSem->id,
            ],
            [
                'college_id'    => $collegeId,
                'course_id'     => $request->course_id[$student->id],
                'year_level_id' => $request->year_level_id[$student->id],
                'section_id'    => $request->section_id[$student->id],
                'status'        => 'ENROLLED',
                'validated_by'  => Auth::id(),
                'validated_at'  => now(),
            ]
        );
        return back()->with('status', 'Student validated successfully.');
    }

    public function bulkValidate(Request $request)
    {
        abort_unless(Auth::user()->isAssessor(), 403);
         if (!$request->has('selected_students')) {
        return back(); // ignore if no students selected (could be a markPaid request)
    }
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
                    'status' => 'ENROLLED',
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                ]
            );
        }

        return back()->with(
            'status',
            count($request->selected_students) . ' students validated successfully.'
        );
    }

    public function template()
    {
        $headers = ['#', 'student_id', 'Student Name', 'Course', 'Year Level', 'Section', 'Contact', 'Email'];
        $filename = 'student_import_template.xlsx';

        return Excel::download(new TemplateExport($headers), $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'student_file' => 'required|file|mimes:xlsx,csv',
        ]);

        try {
            Excel::import(new StudentsImport, $request->file('student_file'));
            return back()->with('status', 'Student list imported successfully. Students remain unvalidated.');
        } catch (\Exception $e) {
            return back()->withErrors(['student_file' => 'Error importing file: '.$e->getMessage()]);
        }
    }

    public function markPaid(Student $student)
    {
        

         abort_unless(Auth::user()->isStudentCoordinator(), 403);

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $enrollment = StudentEnrollment::where([
            'student_id' => $student->id,
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
        ])->firstOrFail();

        $enrollment->update([
            'is_paid' => true,
            'paid_at' => now(),
            'payment_verified_by' => Auth::id(),
        ]);

        return back()->with('status', 'Payment marked as completed.');
    }


}

