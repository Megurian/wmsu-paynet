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

class AdviserStudentUploadController extends Controller
{
   public function index(Request $request)
{
    $adviser = Auth::user();
    $adviserId = $adviser->id;
    $collegeId = $adviser->college_id;
    $adviserCourseId = $adviser->course_id;

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

   $students = Student::query()
    ->whereHas('enrollments', function ($q) use ($adviserId, $collegeId, $adviserCourseId) {
        $q->where('adviser_id', $adviserId)
          ->where('college_id', $collegeId)
          ->where('course_id', $adviserCourseId);
    })

    ->with(['enrollments' => function ($q) {
        $q->orderByDesc('school_year_id')
          ->orderByDesc('semester_id')
          ->limit(1);
    }])

        ->when($request->filled('search'), function ($q) use ($request) {
            $q->where(function ($s) use ($request) {
                $s->where('student_id', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('first_name', 'like', "%{$request->search}%");
            });
        })

        ->with(['enrollments' => function ($q) use ($activeSY, $activeSem) {
            if ($activeSY && $activeSem) {
                $q->where('school_year_id', $activeSY->id)
                  ->where('semester_id', $activeSem->id);
            }
        }])

       ->when($request->filled('year_level_id'), function ($q) use ($request) {
            $q->whereHas('enrollments', function ($e) use ($request) {
                $e->where('year_level_id', $request->year_level_id)
                ->whereIn('id', function ($sub) {
                    $sub->selectRaw('MAX(id)')
                        ->from('student_enrollments')
                        ->groupBy('student_id');
                });
            });
        })

        ->when($request->filled('section_id'), function ($q) use ($request) {
            $q->whereHas('enrollments', function ($e) use ($request) {
                $e->where('section_id', $request->section_id)
                ->latest('school_year_id');
            });
        })

        ->paginate(25)
        ->withQueryString();

    $courses = Course::where('college_id', $collegeId)->get();
    $years = YearLevel::where('college_id', $collegeId)->get();
    $sections = Section::where('college_id', $collegeId)->get();

    $previousEnrollments = StudentEnrollment::with(['course', 'yearLevel', 'section'])
        ->whereIn('student_id', $students->pluck('id'))
        ->where(function($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', '<', $activeSY->id)
              ->orWhere(function($q2) use ($activeSY, $activeSem) {
                  $q2->where('school_year_id', $activeSY->id)
                     ->where('semester_id', '<', $activeSem->id);
              });
        })
        ->orderBy('school_year_id', 'desc')
        ->orderBy('semester_id', 'desc')
        ->get()
        ->groupBy('student_id')
        ->map(fn ($rows) => $rows->first());

    $alpineStudents = $students->getCollection()->map(function ($s) use ($previousEnrollments) {

        $enrollment = $s->enrollments->first(); 
        $prev = $previousEnrollments[$s->id] ?? null;

        return [
            'id' => $s->id,
            'student_id' => $s->student_id,
            'last_name' => $s->last_name,
            'first_name' => $s->first_name,
            'middle_name' => $s->middle_name,

            'course_id' => $enrollment->course_id ?? $prev->course_id ?? null,
            'year_level_id' => $enrollment->year_level_id ?? $prev->year_level_id ?? null,
            'section_id' => $enrollment->section_id ?? $prev->section_id ?? null,

            'status' => $enrollment->status ?? 'NOT_ENROLLED',
            'isCleared' => $enrollment ? $enrollment->cleared_for_enrollment : false,
        ];
    });

    return view('college.students.my-upload', compact(
        'students',
        'alpineStudents',
        'courses',
        'years',
        'sections',
        'previousEnrollments',
        'activeSY',
        'activeSem'
    ));
}


    public function store(Request $request)
    {
        $adviserId = Auth::id();
        $collegeId = Auth::user()->college_id;

        $request->validate([
            'student_id' => 'required|string|max:50',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'course_id' => Auth::user()->role === 'adviser'
                ? 'nullable'
                : 'required|exists:courses,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $student = Student::updateOrCreate(
            ['student_id' => $request->student_id],
            $request->only(['last_name', 'first_name', 'middle_name', 'contact', 'email', 'suffix', 'religion'])
        );

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if ($activeSY && $activeSem) {
            $courseId = Auth::user()->role === 'adviser'
                    ? Auth::user()->course_id
                    : $request->course_id;

            $enrollment = StudentEnrollment::firstOrNew([
                'student_id'     => $student->id,
                'school_year_id' => $activeSY->id,
                'semester_id'    => $activeSem->id,
            ]);

            $enrollment->fill([
                'college_id'    => $collegeId,
                'course_id'     => $courseId,
                'year_level_id' => $request->year_level_id,
                'section_id'    => $request->section_id,
                'adviser_id'    => $adviserId,
            ]);

            if (!$enrollment->exists) {
                $enrollment->status = 'NOT_ENROLLED';
                $enrollment->financial_status = StudentEnrollment::FINANCIAL_UNPAID;
            }

            $enrollment->save();
        }

        return back()->with('status', 'Student uploaded successfully.');
    }

public function reAddOldStudent(Request $request, $studentId)
{
    $adviserId = Auth::id();
    $collegeId = Auth::user()->college_id;

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $prev = StudentEnrollment::where('student_id', $studentId)
        ->where(function($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', '<', $activeSY->id)
              ->orWhere(function($q2) use ($activeSY, $activeSem) {
                  $q2->where('school_year_id', $activeSY->id)
                     ->where('semester_id', '<', $activeSem->id);
              });
        })
        ->latest('id')
        ->first();

    $enrollment = StudentEnrollment::firstOrNew([
        'student_id'     => $studentId,
        'school_year_id' => $activeSY->id,
        'semester_id'    => $activeSem->id,
    ]);

    // Only advance the student if they are still NOT_ENROLLED — never downgrade
    if (!$enrollment->exists || $enrollment->status === 'NOT_ENROLLED') {
            $yearLevelId = $request->year_level_id ?? $enrollment->year_level_id ?? $prev?->year_level_id;
            $sectionId = $request->section_id ?? $enrollment->section_id ?? $prev?->section_id;

            $enrollment->fill([
                'college_id'    => $collegeId,
                'adviser_id'    => $adviserId,
                'status'        => 'FOR_PAYMENT_VALIDATION',
                'advised_at'    => now(),
                'course_id'     => Auth::user()->course_id,
                'year_level_id' => $yearLevelId,
                'section_id'    => $sectionId,
                'financial_status' => StudentEnrollment::FINANCIAL_UNPAID,
            ]);

            $enrollment->save();
    }

    return back()->with('status', 'Student added successfully. Student may now proceed to payment');
}

public function reAddBulk(Request $request)
{
    $request->validate([
        'students'   => 'required|array',
        'students.*' => 'exists:students,id',
    ]);

    $adviserId = Auth::id();
    $collegeId = Auth::user()->college_id;

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    foreach ($request->students as $studentId) {

        $prev = StudentEnrollment::where('student_id', $studentId)
            ->where(function($q) use ($activeSY, $activeSem) {
                $q->where('school_year_id', '<', $activeSY->id)
                  ->orWhere(function($q2) use ($activeSY, $activeSem) {
                      $q2->where('school_year_id', $activeSY->id)
                         ->where('semester_id', '<', $activeSem->id);
                  });
            })
            ->latest('id')
            ->first();

        $latestEnrollment = StudentEnrollment::where('student_id', $studentId)
            ->latest('id')
            ->first();

        $yearLevelId = $prev?->year_level_id
            ?? $latestEnrollment?->year_level_id;

        $sectionId = $prev?->section_id
            ?? $latestEnrollment?->section_id;

        if (!$yearLevelId || !$sectionId) {
            continue; 
        }

        $enrollment = StudentEnrollment::firstOrNew([
            'student_id'     => $studentId,
            'school_year_id' => $activeSY->id,
            'semester_id'    => $activeSem->id,
        ]);

        if (!$enrollment->exists || $enrollment->status === 'NOT_ENROLLED') {

            $enrollment->fill([
                'college_id'       => $collegeId,
                'adviser_id'       => $adviserId,
                'status'           => 'FOR_PAYMENT_VALIDATION',
                'advised_at'       => now(),
                'course_id'        => Auth::user()->course_id,
                'year_level_id'    => $yearLevelId,
                'section_id'       => $sectionId,
                'financial_status' => StudentEnrollment::FINANCIAL_UNPAID,
            ]);

            $enrollment->save();
        }
    }

    return back()->with(
        'status',
        count($request->students) . ' student(s) processed successfully.'
    );
}


public function updateField(Request $request, $id)
{
    $request->validate([
        'field' => 'required|string',
        'value' => 'nullable',
    ]);

    $allowedFields = [
        'year_level_id',
        'section_id',
        'course_id',
    ];

    if (!in_array($request->field, $allowedFields)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid field'
        ], 422);
    }

    $student = Student::findOrFail($id);

    $enrollment = StudentEnrollment::where('student_id', $student->id)
        ->latest('id')
        ->first();

    if (!$enrollment) {
        return response()->json([
            'success' => false,
            'message' => 'Enrollment not found'
        ], 404);
    }

    $enrollment->{$request->field} = $request->value;
    $enrollment->save();

    return response()->json([
        'success' => true,
        'message' => 'Updated successfully'
    ]);
}

private function nextYearLevel($currentYearLevelId)
{
    $current = YearLevel::find($currentYearLevelId);

    if (!$current) return null;

    return YearLevel::where('college_id', Auth::user()->college_id)
        ->where('id', '>', $current->id)
        ->orderBy('id', 'asc')
        ->first();
}

public function promotionPreview()
{
    $adviser = Auth::user();

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $students = Student::whereHas('enrollments', function ($q) use ($adviser) {
            $q->where('adviser_id', $adviser->id);
        })

        ->whereDoesntHave('enrollments', function ($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', $activeSY->id)
              ->where('semester_id', $activeSem->id);
        })

        ->whereHas('enrollments')

        ->with(['enrollments' => function ($q) use ($activeSY, $activeSem) {
            $q->where(function ($sub) use ($activeSY, $activeSem) {
                $sub->where('school_year_id', '<', $activeSY->id)
                    ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                        $q2->where('school_year_id', $activeSY->id)
                           ->where('semester_id', '<', $activeSem->id);
                    });
            })
            ->orderBy('school_year_id', 'desc')
            ->orderBy('semester_id', 'desc');
        }])
        ->get();

    $breakdown = [];

    foreach ($students as $student) {

        $enrollment = $student->enrollments->first();
        if (!$enrollment) continue;

        $from = $enrollment->yearLevel->name ?? 'Unknown';

        $next = $this->nextYearLevel($enrollment->year_level_id);
        $to = $next->name ?? 'Graduated';

        $key = $from . '→' . $to;

        if (!isset($breakdown[$key])) {
            $breakdown[$key] = [
                'from' => $from,
                'to' => $to,
                'count' => 0,
                'students' => []
            ];
        }

        $breakdown[$key]['count']++;

        $breakdown[$key]['students'][] = [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'name' => $student->last_name . ', ' . $student->first_name,
        ];
    }

    return response()->json([
        'breakdown' => array_values($breakdown),
        'total' => $students->count()
    ]);
}

public function promotionExecute()
{
    $adviser = Auth::user();

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $students = Student::whereHas('enrollments', function ($q) use ($adviser) {
            $q->where('adviser_id', $adviser->id);
        })
        ->whereDoesntHave('enrollments', function ($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', $activeSY->id)
              ->where('semester_id', $activeSem->id);
        })
        ->with(['enrollments' => function ($q) use ($activeSY, $activeSem) {
            $q->where(function ($sub) use ($activeSY, $activeSem) {
                $sub->where('school_year_id', '<', $activeSY->id)
                    ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                        $q2->where('school_year_id', $activeSY->id)
                           ->where('semester_id', '<', $activeSem->id);
                    });
            })
            ->orderBy('school_year_id', 'desc')
            ->orderBy('semester_id', 'desc');
        }])
        ->get();

    $updated = 0;

    foreach ($students as $student) {

        $enrollment = $student->enrollments->first();
        if (!$enrollment) continue;

        $nextYear = YearLevel::where('college_id', $adviser->college_id)
            ->where('id', '>', $enrollment->year_level_id)
            ->orderBy('id', 'asc')
            ->first();

        if (!$nextYear) continue;

        $new = StudentEnrollment::firstOrNew([
            'student_id'     => $student->id,
            'school_year_id' => $activeSY->id,
            'semester_id'    => $activeSem->id,
        ]);

        $new->fill([
            'college_id'       => $adviser->college_id,
            'adviser_id'       => $adviser->id,
            'course_id'        => $enrollment->course_id,
            'year_level_id'    => $nextYear->id,
            'section_id'       => $enrollment->section_id,
            'status'           => 'FOR_PAYMENT_VALIDATION',
            'financial_status' => StudentEnrollment::FINANCIAL_UNPAID,
            'advised_at'       => now(),
        ]);

        $new->save();
        $updated++;
    }

    return response()->json([
        'message' => "Promotion completed. Students forwarded to payment validation: $updated"
    ]);
}

public function promoteAndPay(Request $request, $studentId)
{
    $adviser = Auth::user();

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $student = Student::findOrFail($studentId);

    $enrollment = StudentEnrollment::where('student_id', $studentId)
        ->latest('id')
        ->first();

    if (!$enrollment) {
        return response()->json([
            'message' => 'No enrollment found'
        ], 404);
    }

    $yearLevelId = $request->year_level_id;

    if ($request->promote) {
        $nextYear = YearLevel::where('college_id', $adviser->college_id)
            ->where('id', '>', $enrollment->year_level_id)
            ->orderBy('id')
            ->first();

        $yearLevelId = $nextYear?->id ?? $enrollment->year_level_id;
    }

    $new = StudentEnrollment::firstOrNew([
        'student_id' => $studentId,
        'school_year_id' => $activeSY->id,
        'semester_id' => $activeSem->id,
    ]);

    $new->fill([
        'college_id' => $adviser->college_id,
        'adviser_id' => $adviser->id,
        'course_id' => $enrollment->course_id,
        'year_level_id' => $yearLevelId,
        'section_id' => $request->section_id ?? $enrollment->section_id,
        'status' => 'FOR_PAYMENT_VALIDATION',
        'financial_status' => StudentEnrollment::FINANCIAL_UNPAID,
        'advised_at' => now(),
    ]);

    $new->save();

    return response()->json([
        'message' => 'Student promoted and sent to payment successfully'
    ]);
}

public function bulkPromoteAndPay(Request $request)
{
    $adviser = Auth::user();

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    foreach ($request->students as $s) {

        $studentId = $s['id'];

        $latest = StudentEnrollment::where('student_id', $studentId)
            ->latest('id')
            ->first();

        if (!$latest) continue;

        $yearLevelId = $s['next_year_level_id'];

        if ($s['promote']) {
            $nextYear = YearLevel::where('college_id', $adviser->college_id)
                ->where('id', '>', $latest->year_level_id)
                ->orderBy('id')
                ->first();

            $yearLevelId = $nextYear?->id ?? $latest->year_level_id;
        }

        $new = StudentEnrollment::firstOrNew([
            'student_id' => $studentId,
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
        ]);

        $new->fill([
            'college_id' => $adviser->college_id,
            'adviser_id' => $adviser->id,
            'course_id' => $latest->course_id,
            'year_level_id' => $yearLevelId,
            'section_id' => $s['section_id'],
            'status' => 'FOR_PAYMENT_VALIDATION',
            'financial_status' => StudentEnrollment::FINANCIAL_UNPAID,
            'advised_at' => now(),
        ]);

        $new->save();
    }

    return response()->json([
        'message' => 'Bulk students processed successfully'
    ]);
}

}

