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
            'student', 'course', 'yearLevel', 'section', 'schoolYear', 'semester'
        ])
        ->join('students', 'student_enrollments.student_id', '=', 'students.id')
        ->where('student_enrollments.college_id', $collegeId)
        ->when($selectedSY, fn ($q) =>
            $q->where('student_enrollments.school_year_id', $selectedSY)
        )
        ->when($selectedSem, fn ($q) =>
            $q->whereHas('semester', fn ($s) =>
                $s->where('name', $selectedSem)
            )
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

}
