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

        // Get only students who are advised and may proceed to payment
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

        // Only fees approved by the college dean
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

        // Mark fees as paid (simplified; you may want a Payment model)
        $studentEnrollment->update([
            'is_paid' => true,
            'status' => 'PAID',
        ]);

        return back()->with('status', 'Payment collected successfully.');
    }

    public function searchStudents(Request $request)
    {
        $search = $request->query('q');
        $students = Student::whereHas('enrollments', function($q) {
            $q->where('status', 'FOR_PAYMENT_VALIDATION');
        })
        ->where(function($q) use ($search) {
            $q->where('student_id', 'like', "%$search%")
              ->orWhere('first_name', 'like', "%$search%")
              ->orWhere('last_name', 'like', "%$search%");
        })
        ->get();

        return response()->json($students);
    }
}