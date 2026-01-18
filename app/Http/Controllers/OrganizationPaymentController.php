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
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'student_id' => $s->student_id,
                'name' => trim("{$s->last_name}, {$s->first_name} {$s->middle_name}"),
            ]);

        return response()->json($students);
    }

}
