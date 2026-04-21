<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;

class AdviserStudentUploadController extends Controller
{
   private function getPreviousTermEnrollment(int $studentId, ?SchoolYear $activeSY, ?Semester $activeSem): ?StudentEnrollment
   {
       if (!$activeSY || !$activeSem) {
           return null;
       }

       return StudentEnrollment::where('student_id', $studentId)
           ->where(function ($q) use ($activeSY, $activeSem) {
               $q->where('school_year_id', '<', $activeSY->id)
                 ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                     $q2->where('school_year_id', $activeSY->id)
                        ->where('semester_id', '<', $activeSem->id);
                 });
           })
           ->orderByDesc('school_year_id')
           ->orderByDesc('semester_id')
           ->orderByDesc('id')
           ->first();
   }

   private function canAdviseForCurrentSemester(?StudentEnrollment $currentEnrollment, ?StudentEnrollment $previousEnrollment): bool
   {
       if ($currentEnrollment) {
           return $currentEnrollment->status === StudentEnrollment::NOT_ENROLLED;
       }

       return ! $previousEnrollment
           || $previousEnrollment->is_void
           || $previousEnrollment->status === StudentEnrollment::ENROLLED;
   }

   public function index(Request $request)
{
    $adviser = Auth::user();
    $adviserId = $adviser->id;
    $collegeId = $adviser->college_id;
    $adviserCourseId = $adviser->course_id;
    $adviser = Auth::user()->loadMissing('course');

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();
    $activeSemesterName = $activeSem?->name;

   $students = Student::query()
        ->where('is_graduated', false)
        ->whereHas('enrollments', function ($q) use ($adviserId, $collegeId, $adviserCourseId) {
            $q->where('adviser_id', $adviserId)
              ->where('college_id', $collegeId)
              ->where('course_id', $adviserCourseId);
        })
        ->with('currentEnrollment')
        ->when($request->filled('search'), function ($q) use ($request) {
            $q->where(function ($s) use ($request) {
                $s->where('student_id', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('first_name', 'like', "%{$request->search}%");
            });
        })

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
                $e->where('section_id', $request->section_id);
            });
        })

        ->paginate(60)
        ->withQueryString();

    $courses = Course::where('college_id', $collegeId)->get();
    $years = YearLevel::where('college_id', $collegeId)->get();
    $sections = Section::where('college_id', $collegeId)->get();

    $previousEnrollments = collect();
    if ($activeSY && $activeSem) {
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
    }

    $alpineStudents = $students->getCollection()->map(function ($s) use ($previousEnrollments) {

        $enrollment = $s->currentEnrollment;
        $prev = $previousEnrollments[$s->id] ?? null;
        $canAdvise = $this->canAdviseForCurrentSemester($enrollment, $prev);

        return [
            'id' => $s->id,
            'student_id' => $s->student_id,
           'last_name' => strtoupper($s->last_name),
            'first_name' => strtoupper($s->first_name),
            'middle_name' => $s->middle_name ? strtoupper($s->middle_name) : null,

            'course_id' => $enrollment->course_id ?? $prev->course_id ?? null,
            'year_level_id' => $enrollment->year_level_id ?? $prev->year_level_id ?? null,
            'section_id' => $enrollment->section_id ?? $prev->section_id ?? null,

            'has_current_enrollment' => (bool) $enrollment,
            'status' => $enrollment->status ?? StudentEnrollment::NOT_ENROLLED,
            'can_advise' => $canAdvise,
            'financial_status' => $enrollment->financial_status ?? StudentEnrollment::FINANCIAL_UNPAID,
            'previous_status' => $prev?->status,
            'previous_is_void' => $prev?->is_void ?? false,
            'previous_financial_status' => $prev?->financial_status,
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
        'activeSem',
        'activeSemesterName',
       'adviser'
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
            [
                'last_name'   => strtoupper($request->last_name),
                'first_name'  => strtoupper($request->first_name),
                'middle_name' => $request->middle_name ? strtoupper($request->middle_name) : null,
                'contact'     => $request->contact,
                'email'       => $request->email,
                'suffix'      => $request->suffix,
                'religion'    => $request->religion,
            ]
        );

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();
        $previousEnrollment = $this->getPreviousTermEnrollment($student->id, $activeSY, $activeSem);

        if ($activeSY && $activeSem) {
            $courseId = Auth::user()->role === 'adviser'
                    ? Auth::user()->course_id
                    : $request->course_id;

            $enrollment = StudentEnrollment::firstOrNew([
                'student_id'     => $student->id,
                'school_year_id' => $activeSY->id,
                'semester_id'    => $activeSem->id,
            ]);

            if (! $this->canAdviseForCurrentSemester($enrollment->exists ? $enrollment : null, $previousEnrollment)) {
                return back()->withErrors([
                    'status' => 'This student still has an active previous-semester record. Void it before advising again.',
                ]);
            }

            $enrollment->fill([
                'college_id'    => $collegeId,
                'course_id'     => $courseId,
                'year_level_id' => $request->year_level_id,
                'section_id'    => $request->section_id,
                'adviser_id'    => $adviserId,
            ]);

            if (!$enrollment->exists) {
                $enrollment->status = StudentEnrollment::FOR_PAYMENT_VALIDATION;
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

    if (! $activeSY || ! $activeSem) {
        return back()->withErrors([
            'academic_period' => 'No active school year or semester. Contact OSA for confirmation before re-adding students.'
        ]);
    }

    $previousEnrollment = $this->getPreviousTermEnrollment($studentId, $activeSY, $activeSem);

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

    if (! $this->canAdviseForCurrentSemester($enrollment->exists ? $enrollment : null, $previousEnrollment)) {
        return back()->withErrors([
            'status' => 'This student still has an active previous-semester record. Void it before advising again.',
        ]);
    }

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

    if (! $activeSY || ! $activeSem) {
        return back()->withErrors([
            'academic_period' => 'No active school year or semester. Contact OSA for confirmation before re-adding students.'
        ]);
    }

    foreach ($request->students as $studentId) {
        $previousEnrollment = $this->getPreviousTermEnrollment($studentId, $activeSY, $activeSem);

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

        if (! $this->canAdviseForCurrentSemester($enrollment->exists ? $enrollment : null, $previousEnrollment)) {
            continue;
        }

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
        'field' => ['required', 'string', Rule::in(['year_level_id', 'section_id', 'course_id'])],
        'value' => ['required', 'integer'],
    ]);

    $allowedFields = [
        'year_level_id',
        'section_id',
        'course_id',
    ];

    if (! in_array($request->field, $allowedFields, true)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid field',
        ], 422);
    }

    $existsRule = match ($request->field) {
        'year_level_id' => Rule::exists('year_levels', 'id'),
        'section_id' => Rule::exists('sections', 'id'),
        'course_id' => Rule::exists('courses', 'id'),
    };

    validator($request->all(), [
        'value' => ['required', 'integer', $existsRule],
    ])->validate();

    $student = Student::findOrFail($id);

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    if (! $activeSY || ! $activeSem) {
        return response()->json([
            'success' => false,
            'message' => 'Active school year or semester not found',
        ], 422);
    }

    $enrollment = StudentEnrollment::where('student_id', $student->id)
        ->where('school_year_id', $activeSY->id)
        ->where('semester_id', $activeSem->id)
        ->first();

    if (! $enrollment) {
        return response()->json([
            'success' => false,
            'message' => 'Current term enrollment not found'
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

    $baseEnrollment = $enrollment;

    if (!$baseEnrollment->year_level_id) {
        $baseEnrollment = StudentEnrollment::where('student_id', $studentId)
            ->whereNotNull('year_level_id')
            ->latest('id')
            ->first() ?? $enrollment;
    }

    $blockedFinancialStatuses = [
        StudentEnrollment::FINANCIAL_UNPAID,
        StudentEnrollment::FINANCIAL_PARTIALLY_PAID,
        StudentEnrollment::FINANCIAL_DEFAULT,
        StudentEnrollment::FINANCIAL_BAD_DEBT,
    ];

    $previousEnrollment = $this->getPreviousTermEnrollment($studentId, $activeSY, $activeSem);

    if (! $previousEnrollment) {
        $previousEnrollment = StudentEnrollment::where('student_id', $studentId)
            ->orderByDesc('school_year_id')
            ->orderByDesc('semester_id')
            ->orderByDesc('id')
            ->first();
    }

    if ($previousEnrollment && ! $previousEnrollment->is_void && in_array($previousEnrollment->financial_status, $blockedFinancialStatuses, true)) {
        return response()->json([
            'message' => 'Student cannot proceed to payment because previous semester fees are unsettled.'
        ], 422);
    }

    $graduated = $request->boolean('graduated');

    if ($graduated) {
        Student::where('id', $student->id)->update([
            'is_graduated' => true,
        ]);

        return response()->json([
            'message' => 'Student marked graduated successfully'
        ]);
    }

    $canPromote = ! $graduated
        && $request->boolean('promote')
        && $baseEnrollment->status === 'ENROLLED'
        && $baseEnrollment->financial_status === StudentEnrollment::FINANCIAL_PAID
        && optional($activeSem)->name === '1st SEMESTER';

    $yearLevelId = $request->filled('year_level_id')
        ? $request->year_level_id
        : $baseEnrollment->year_level_id;

    if ($canPromote) {
        $nextYear = YearLevel::where('college_id', $adviser->college_id)
            ->where('id', '>', $baseEnrollment->year_level_id)
            ->orderBy('id')
            ->first();

        $yearLevelId = $nextYear?->id ?? $baseEnrollment->year_level_id;
    }

    if (!$yearLevelId) {
        return response()->json([
            'message' => 'Unable to determine student year level for promotion.'
        ], 422);
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
        'message' => $graduated
            ? 'Student marked graduated and sent to payment successfully'
            : 'Student promoted and sent to payment successfully'
    ]);
}

public function bulkPromoteAndPay(Request $request)
{
    $request->validate([
        'students' => 'required|array|min:1',
        'students.*.id' => 'required|exists:students,id',
        'students.*.promote' => 'required|boolean',
        'students.*.graduated' => 'required|boolean',
        'students.*.section_id' => 'required|exists:sections,id',
        'students.*.next_year_level_id' => 'nullable|exists:year_levels,id',
    ]);

    $adviser = Auth::user();

    $activeSY = SchoolYear::where('is_active', true)->first();
    $activeSem = Semester::where('is_active', true)->first();

    $blockedFinancialStatuses = [
        StudentEnrollment::FINANCIAL_UNPAID,
        StudentEnrollment::FINANCIAL_PARTIALLY_PAID,
        StudentEnrollment::FINANCIAL_DEFAULT,
        StudentEnrollment::FINANCIAL_BAD_DEBT,
    ];

    $processed = 0;
    $graduated = 0;
    $skipped = 0;

    foreach ($request->students as $s) {
        $studentId = $s['id'];
        $student = Student::find($studentId);

        if (!$student) {
            $skipped++;
            continue;
        }

        $currentEnrollment = StudentEnrollment::where('student_id', $studentId)
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->latest('id')
            ->first();

        $previousEnrollment = $this->getPreviousTermEnrollment($studentId, $activeSY, $activeSem);

        if (! $this->canAdviseForCurrentSemester($currentEnrollment, $previousEnrollment)) {
            $skipped++;
            continue;
        }

        if ($previousEnrollment && ! $previousEnrollment->is_void && in_array($previousEnrollment->financial_status, $blockedFinancialStatuses, true)) {
            $skipped++;
            continue;
        }

        if ($s['graduated']) {
            Student::where('id', $student->id)->update([
                'is_graduated' => true,
            ]);
            $graduated++;
            continue;
        }

        $latest = StudentEnrollment::where('student_id', $studentId)
            ->latest('id')
            ->first();

        if (!$latest) {
            $skipped++;
            continue;
        }

        $baseEnrollment = $latest;

        if (!$baseEnrollment->year_level_id) {
            $baseEnrollment = StudentEnrollment::where('student_id', $studentId)
                ->whereNotNull('year_level_id')
                ->latest('id')
                ->first() ?? $latest;
        }

        $canPromote = $s['promote']
            && $baseEnrollment->status === StudentEnrollment::ENROLLED
            && $baseEnrollment->financial_status === StudentEnrollment::FINANCIAL_PAID
            && optional($activeSem)->name === '1st SEMESTER';

        $yearLevelId = $s['next_year_level_id'] ?? $baseEnrollment->year_level_id;

        if ($canPromote) {
            $nextYear = YearLevel::where('college_id', $adviser->college_id)
                ->where('id', '>', $baseEnrollment->year_level_id)
                ->orderBy('id')
                ->first();

            $yearLevelId = $nextYear?->id ?? $baseEnrollment->year_level_id;
        }

        if (!$yearLevelId) {
            $skipped++;
            continue;
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
            'status' => StudentEnrollment::FOR_PAYMENT_VALIDATION,
            'financial_status' => StudentEnrollment::FINANCIAL_UNPAID,
            'advised_at' => now(),
        ]);

        $new->save();
        $processed++;
    }

    return response()->json([
        'message' => "Bulk students processed successfully. Processed: $processed. Graduated: $graduated. Skipped: $skipped.",
        'processed' => $processed,
        'graduated' => $graduated,
        'skipped' => $skipped,
    ]);
}

}

