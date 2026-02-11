<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Fee;
use App\Models\SchoolYear;
use App\Models\Semester;

class AdminCashieringController extends Controller
{
    public function index(Request $request)
    {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $students = Student::whereHas('enrollments', function($q) use ($activeSY, $activeSem) {
            $q->where('status', 'FOR_PAYMENT_VALIDATION')
              ->where('school_year_id', $activeSY->id)
              ->where('semester_id', $activeSem->id);
        })
        ->when($request->filled('search'), function($q) use ($request) {
            $search = $request->search;
            $q->where(function($s) use ($search) {
                $s->where('student_id', 'like', "%$search%")
                  ->orWhere('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%");
            });
        })
        ->with(['enrollments' => function($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', $activeSY->id)
              ->where('semester_id', $activeSem->id);
        }])
        ->get();

        $fees = Fee::where('status', 'APPROVED')->get();

        return view('college.cashiering', compact('students', 'fees'));
    }

    public function collectPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required|array',
            'fee_ids.*' => 'exists:college_fees,id',
        ]);

        $studentEnrollment = StudentEnrollment::where('student_id', $request->student_id)
            ->where('status', 'FOR_PAYMENT_VALIDATION')
            ->firstOrFail();

        $studentEnrollment->update([
            'is_paid' => true,
            'status' => 'PAID',
        ]);

        return response()->json(['message' => 'Payment collected successfully.']);
    }
   public function searchAdvisedStudents(Request $request)
    {
        $query = $request->query('q');

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $students = Student::whereHas('enrollments', function($q) use ($activeSY, $activeSem) {
            $q->where('status', 'FOR_PAYMENT_VALIDATION')
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id);
        })
        ->where(function($q) use ($query) {
            $q->where('student_id', 'like', "%$query%")
            ->orWhere('first_name', 'like', "%$query%")
            ->orWhere('last_name', 'like', "%$query%");
        })
        ->take(10)
        ->get(['id', 'student_id', 'first_name', 'last_name']);

        return response()->json($students);
    }

    public function getStudentDetails(Request $request, $studentId)
    {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $student = Student::with(['enrollments' => function($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id);
        }])->findOrFail($studentId);

        $enrollment = $student->enrollments->first();

        $fees = Fee::where('status', 'APPROVED')->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'course' => $enrollment?->course->name ?? null,
                'year_level' => $enrollment?->yearLevel->name ?? null,
                'section' => $enrollment?->section->name ?? null,
            ],
            'fees' => $fees
        ]);
    }

}