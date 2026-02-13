<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\Semester;

class OrganizationPaymentController extends Controller
{
    public function searchStudents(Request $request)
    {
        $query = $request->q;
        if (!$query) {
            return response()->json([]);
        }

        $user = Auth::user();
        $collegeId = $user->organization->college_id ?? null;
        if (!$collegeId) {
            return response()->json([]);
        }

        // Get active school year and semester
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        // Only return students who are advised and may proceed to payment
        $students = Student::where('college_id', $collegeId)
            ->whereHas('enrollments', function ($q) use ($activeSY, $activeSem) {
                $q->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED'])
                ->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id);
            })
            ->where(function ($q) use ($query) {
                $q->where('student_id', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $enrollment = $s->enrollments->first();
                return [
                    'id' => $s->id,
                    'student_id' => $s->student_id,
                    'first_name' => $s->first_name,
                    'last_name' => $s->last_name,
                    'name' => trim("{$s->last_name}, {$s->first_name} {$s->middle_name}"),
                    'email' => $s->email,
                    'course' => $enrollment?->course?->name,
                    'year' => $enrollment?->yearLevel?->name,
                    'section' => $enrollment?->section?->name,
                ];
            });

        return response()->json($students);
    }
}