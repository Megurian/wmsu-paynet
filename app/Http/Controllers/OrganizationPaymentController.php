<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\StudentEnrollment;
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

        $students = Student::where('college_id', $collegeId)
            ->where(function ($q) use ($query) {
                $q->where('student_id', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->with([
                'enrollments' => function ($q) {
                    $q->latest()->limit(1);
                },
                'enrollments.course',
                'enrollments.yearLevel',
                'enrollments.section',
            ])
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $enrollment = $s->enrollments->first();

                return [
                    'id' => $s->id,
                    'student_id' => $s->student_id,
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
