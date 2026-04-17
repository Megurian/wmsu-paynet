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
                ->latest('school_year_id');
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
}

