<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\StudentEnrollment;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Payment;
use Illuminate\Http\Request;

class CollegeHistoryController extends Controller
{

public function history(Request $request)
{
    $collegeId = Auth::user()->college_id;

    $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
    $semesters   = Semester::orderBy('id')->get();

    $activeSY  = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $selectedSY  = $request->school_year ?? $activeSY?->id;
    $selectedSem = $request->semester ?? $activeSem?->name; 

$selectedSchoolYear = SchoolYear::find($selectedSY);
$selectedSemester   = Semester::where('name', $selectedSem)->first();

    $selectedCourse  = $request->course ?? null;
    $selectedYear    = $request->year ?? null;
    $selectedSection = $request->section ?? null;
    $selectedAdviser = $request->adviser ?? null;
    $selectedStatus  = $request->status ?? null;

    $courses  = \App\Models\Course::where('college_id', $collegeId)->get();
    $years    = \App\Models\YearLevel::where('college_id', $collegeId)->get();
    $sections = \App\Models\Section::where('college_id', $collegeId)->get();
    $advisers = \App\Models\User::where('college_id', $collegeId)
                    ->where('role', 'adviser')
                    ->get();

    $students = StudentEnrollment::with([
        'student',
        'course',
        'yearLevel',
        'section',
        'schoolYear',
        'semester',
        'adviser'
    ])
        ->join('students', 'student_enrollments.student_id', '=', 'students.id')
        ->where('student_enrollments.college_id', $collegeId)
        ->when($selectedSY, fn($q) =>
            $q->where('student_enrollments.school_year_id', $selectedSY)
        )
        ->when($selectedSem, fn($q) =>
            $q->whereHas('semester', fn($s) => $s->where('name', $selectedSem))
        )
        ->when($selectedCourse, fn($q) =>
            $q->where('student_enrollments.course_id', $selectedCourse)
        )
        ->when($selectedYear, fn($q) =>
            $q->where('student_enrollments.year_level_id', $selectedYear)
        )
        ->when($selectedSection, fn($q) =>
            $q->where('student_enrollments.section_id', $selectedSection)
        )
        ->when($selectedAdviser, fn($q) =>
            $q->where('student_enrollments.adviser_id', $selectedAdviser)
        )
        ->when($selectedStatus, function ($q) use ($selectedStatus) {
            match ($selectedStatus) {
                'assessed' => $q->whereNotNull('assessed_at'),
                'to_assess' => $q->whereNull('assessed_at')
                                 ->whereNotNull('validated_at'),
                'pending_payment' => $q->whereNull('validated_at')
                                        ->whereNotNull('advised_at'),
                'not_enrolled' => $q->whereNull('advised_at'),
                default => null
            };
        })
        ->orderBy('students.last_name')
        ->orderBy('students.first_name')
        ->select('student_enrollments.*')
        ->get();

    $payments = Payment::with(['student', 'fees', 'organization'])
        ->where('school_year_id', $selectedSY)
        ->whereHas('semester', fn($s) => $s->where('name', $selectedSem))
        ->orderBy('created_at', 'desc')
        ->get();

    return view('college.history', compact(
        'students',
        'payments',
        'schoolYears',
        'semesters',
        'selectedSY',
        'selectedSem',
        'selectedSchoolYear',
        'selectedSemester',
        'courses',
        'years',
        'sections',
        'advisers',
        'selectedCourse',
        'selectedYear',
        'selectedSection',
        'selectedAdviser',
        'selectedStatus'
    ));
}

    public function showStudentHistory($studentId)
    {
        $studentEnrollments = StudentEnrollment::with([
            'student',
            'course',
            'yearLevel',
            'section',
            'schoolYear',
            'semester',
            'adviser'
        ])
            ->where('student_id', $studentId)
            ->orderBy('school_year_id', 'desc')
            ->orderBy('semester_id', 'desc')
            ->get();

        if ($studentEnrollments->isEmpty()) {
            abort(404, 'Student history not found.');
        }

        $studentInfo = $studentEnrollments->first()->student;

        $collegeId = auth()->user()->college_id;

        $fees = \App\Models\Fee::with('organization')
            ->where('status', 'approved')
            ->where(function ($q) use ($collegeId) {
                $q->where('fee_scope', 'university-wide')
                    ->orWhere('fee_scope', 'college')
                    ->orWhereHas('organization', fn($org) => $org->where('college_id', $collegeId));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $payments = Payment::with('fees')
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('transaction_id'); 

        return view('college.student-history-info', compact('studentEnrollments', 'studentInfo', 'fees', 'payments'));
    }
}
