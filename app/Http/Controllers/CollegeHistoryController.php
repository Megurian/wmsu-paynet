<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\StudentEnrollment;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;

class CollegeHistoryController extends Controller
{
    public function history(Request $request)
    {
        $collegeId = Auth::user()->college_id;

        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters = Semester::orderBy('id')->get();

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $selectedSY = $request->school_year ?? $activeSY?->id;
        $selectedSem = $request->semester ?? '1st';

        $selectedSchoolYear = SchoolYear::find($selectedSY);
        $selectedSemester   = Semester::where('name', $selectedSem)->first();

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
            ->when($selectedSY, fn($q) => $q->where('student_enrollments.school_year_id', $selectedSY))
            ->when(
                $selectedSem,
                fn($q) =>
                $q->whereHas('semester', fn($s) => $s->where('name', $selectedSem))
            )
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->select('student_enrollments.*')
            ->get();



        return view('college.history', compact(
            'students',
            'schoolYears',
            'semesters',
            'selectedSY',
            'selectedSem',
            'selectedSchoolYear',
            'selectedSemester'
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

        $payments = \App\Models\Payment::with('fees')
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('transaction_id'); 

        return view('college.student-history-info', compact('studentEnrollments', 'studentInfo', 'fees', 'payments'));
    }
}
