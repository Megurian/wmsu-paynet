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
use App\Imports\StudentsImportPreview;

class ValidateStudentsController extends Controller
{


    public function index(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $studentsQuery = Student::whereHas('enrollments', function ($e) use ($collegeId, $request) {
                $e->whereIn('id', function ($q) {
                    $q->selectRaw('MAX(id)')
                        ->from('student_enrollments')
                        ->groupBy('student_id');
                })
                ->where('college_id', $collegeId);

                if ($request->course) {
                    $e->where('course_id', $request->course);
                }
                if ($request->year) {
                    $e->where('year_level_id', $request->year);
                }
                if ($request->section) {
                    $e->where('section_id', $request->section);
                }
            })
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
            'students',
            'activeSY',
            'activeSem',
            'courses',
            'years',
            'sections',
            'activeEnrollments',
            'previousEnrollments'
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
                'assessed_by'   => Auth::id(),
                'assessed_at'   => now(),
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
                    'school_year_id' => $activeSY->id,
                    'semester_id'    => $activeSem->id,
                ],
                [
                    'college_id'     => $collegeId,
                    'course_id'      => $request->course_id[$studentId],
                    'year_level_id'  => $request->year_level_id[$studentId],
                    'section_id'     => $request->section_id[$studentId],
                    'status' => 'ENROLLED',
                    'assessed_by' => Auth::id(),
                    'assessed_at' => now(),
                ]
            );
        }

        return back()->with(
            'status',
            count($request->selected_students) . ' students validated successfully.'
        );
    }

    // public function template()
    // {
    //     $headers = ['#', 'student_id', 'Student Name', 'Course', 'Year Level', 'Section', 'Contact', 'Email'];
    //     $filename = 'student_import_template.xlsx';

    //     return Excel::download(new TemplateExport($headers), $filename);
    // }

    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'student_file' => 'required|file|mimes:xlsx,csv',
    //     ]);

    //     try {
    //         Excel::import(new StudentsImport, $request->file('student_file'));
    //         return back()->with('status', 'Student list imported successfully. Students remain unvalidated.');
    //     } catch (\Exception $e) {
    //         return back()->withErrors(['student_file' => 'Error importing file: '.$e->getMessage()]);
    //     }
    // }

    public function import(Request $request)
    {
        $request->validate([
            'student_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $importer = new StudentsImport();
        Excel::import($importer, $request->file('student_file'));

        $result = $importer->getResult();

        $message = "{$result['created']} new student(s) added, {$result['updated']} updated.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} row(s) skipped (student ID exists with a different last name).";
        }

        return redirect()->back()->with('import_success', $message)
            ->with('import_skipped', $result['skipped_rows']);
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'student_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $collegeId = Auth::user()->college_id;

        // Read file into collection using Maatwebsite (first sheet, with heading row)
        $collection = Excel::toCollection(new \App\Imports\StudentsImportPreview(), $request->file('student_file'));
        $rows = $collection->first() ?? collect();

        $existing_match    = [];   // student_id + last_name both match → will be updated
        $existing_mismatch = [];   // student_id matches but last_name differs → will be skipped
        $new_count         = 0;

        foreach ($rows as $row) {
            $row = collect($row)->mapWithKeys(fn($v, $k) => [strtolower(str_replace(' ', '_', $k)) => $v]);

            $studentId = trim($row['student_id'] ?? '');
            $lastName  = trim($row['last_name']  ?? '');

            if (!$studentId || !$lastName) {
                continue;
            }

            $existing = \App\Models\Student::where('student_id', $studentId)->first();

            if ($existing) {
                // Check if this student has an enrollment in the current college
                $inCollege = $existing->enrollments()
                    ->where('college_id', $collegeId)
                    ->exists();

                if (!$inCollege) {
                    // Student exists globally but not in this college, treat as new
                    $new_count++;
                    continue;
                }

                if (strtolower($existing->last_name) === strtolower($lastName)) {
                    $existing_match[] = [
                        'student_id' => $studentId,
                        'last_name'  => $lastName,
                        'first_name' => trim($row['first_name'] ?? ''),
                    ];
                } else {
                    $existing_mismatch[] = [
                        'student_id'     => $studentId,
                        'file_last_name' => $lastName,
                        'db_last_name'   => $existing->last_name,
                        'first_name'     => trim($row['first_name'] ?? ''),
                    ];
                }
            } else {
                $new_count++;
            }
        }

        return response()->json([
            'existing_match'    => $existing_match,
            'existing_mismatch' => $existing_mismatch,
            'new_count'         => $new_count,
        ]);
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
        ]);

        return back()->with('status', 'Payment marked as completed.');
    }

    public function clearForEnrollment(Student $student)
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
            'cleared_for_enrollment' => true,
            'status' => 'PAID',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return back()->with('status', 'Student cleared for enrollment.');
    }

    public function getFeesForStudent(Student $student)
    {
        $collegeId = Auth::user()->college_id;
        $organizationIds = \App\Models\Organization::where('college_id', $collegeId)
            ->orWhereHas('motherOrganization', fn($q) => $q->where('college_id', $collegeId))
            ->pluck('id');

        $orgFees = \App\Models\Fee::whereIn('organization_id', $organizationIds)
            ->where('status', 'APPROVED') 
            ->with([
                'organization',
                'payments' => fn($q) => $q->where('student_id', $student->id)
            ]);
        $collegeFees = \App\Models\Fee::where('college_id', $collegeId)
            ->whereNull('organization_id')
            ->where('status', 'APPROVED')
            ->with([
                'organization', 
                'payments' => fn($q) => $q->where('student_id', $student->id)
            ]);

        $fees = $orgFees->union($collegeFees)->get();

        return response()->json($fees);
    }
}
