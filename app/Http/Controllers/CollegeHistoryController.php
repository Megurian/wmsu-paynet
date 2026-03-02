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

        // Dropdown data
        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters   = Semester::orderBy('id')->get();
        $courses     = \App\Models\Course::where('college_id', $collegeId)->get();
        $years       = \App\Models\YearLevel::where('college_id', $collegeId)->get();
        $sections    = \App\Models\Section::where('college_id', $collegeId)->get();
        $advisers    = \App\Models\User::where('college_id', $collegeId)
            ->where('role', 'adviser')
            ->get();

        // Determine active / selected school year and semester
        $activeSelection = $this->getActiveSchoolYearAndSemester($request);
        extract($activeSelection);

        // Additional filters
        $selectedCourse  = $request->course ?? null;
        $selectedYear    = $request->year ?? null;
        $selectedSection = $request->section ?? null;
        $selectedAdviser = $request->adviser ?? null;
        $selectedStatus  = $request->status ?? null;

        // Students
        $students = $this->getStudents(
            $collegeId,
            $selectedSY,
            $selectedSem,
            $selectedCourse,
            $selectedYear,
            $selectedSection,
            $selectedAdviser,
            $selectedStatus
        );

        // Payments
        $payments = $this->getPayments($selectedSY, $selectedSem);

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
        $collegeId   = auth()->user()->college_id;

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


    private function getActiveSchoolYearAndSemester(Request $request): array
    {
        $activeSY  = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $selectedSY  = $request->school_year ?? $activeSY?->id;
        $selectedSem = $request->semester ?? $activeSem?->name;

        $selectedSchoolYear = SchoolYear::find($selectedSY);
        $selectedSemester   = Semester::where('name', $selectedSem)->first();

        return [
            'selectedSY' => $selectedSY,
            'selectedSem' => $selectedSem,
            'selectedSchoolYear' => $selectedSchoolYear,
            'selectedSemester' => $selectedSemester,
        ];
    }

    private function getStudents(
        int $collegeId,
        ?int $selectedSY,
        ?string $selectedSem,
        ?int $selectedCourse,
        ?int $selectedYear,
        ?int $selectedSection,
        ?int $selectedAdviser,
        ?string $selectedStatus
    ) {
        $search = request('search'); 

        return StudentEnrollment::with([
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
            ->when($selectedSY, fn($q) => $q->where('student_enrollments.school_year_id', $selectedSY))
            ->when($selectedSem, fn($q) => $q->whereHas('semester', fn($s) => $s->where('name', $selectedSem)))
            ->when($selectedCourse, fn($q) => $q->where('student_enrollments.course_id', $selectedCourse))
            ->when($selectedYear, fn($q) => $q->where('student_enrollments.year_level_id', $selectedYear))
            ->when($selectedSection, fn($q) => $q->where('student_enrollments.section_id', $selectedSection))
            ->when($selectedAdviser, fn($q) => $q->where('student_enrollments.adviser_id', $selectedAdviser))
            ->when($selectedStatus, function ($q) use ($selectedStatus) {
                match ($selectedStatus) {
                    'assessed' => $q->whereNotNull('assessed_at'),
                    'to_assess' => $q->whereNull('assessed_at')->whereNotNull('validated_at'),
                    'pending_payment' => $q->whereNull('validated_at')->whereNotNull('advised_at'),
                    'not_enrolled' => $q->whereNull('advised_at'),
                    default => null
                };
            })
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('students.first_name', 'like', "%{$search}%")
                        ->orWhere('students.last_name', 'like', "%{$search}%")
                        ->orWhere('students.student_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->select('student_enrollments.*')
            ->get();
    }


    private function getPayments(?int $selectedSY, ?string $selectedSem)
    {
        return Payment::with(['student', 'fees', 'organization'])
            ->where('school_year_id', $selectedSY)
            ->when($selectedSem, fn($q) => $q->whereHas('semester', fn($s) => $s->where('name', $selectedSem)))
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
