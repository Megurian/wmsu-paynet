<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\StudentEnrollment;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CollegeHistoryController extends Controller
{
    public function history(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $schoolYears   = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters     = Semester::orderBy('id')->get();
        $courses       = \App\Models\Course::where('college_id', $collegeId)->get();
        $years         = \App\Models\YearLevel::where('college_id', $collegeId)->get();
        $sections      = \App\Models\Section::where('college_id', $collegeId)->get();
        $advisers      = \App\Models\User::where('college_id', $collegeId)->where('role', 'adviser')->get();
        $organizations = \App\Models\Organization::where('college_id', $collegeId)->get();

        $activeSelection = $this->getActiveSchoolYearAndSemester($request);
        extract($activeSelection);

        $selectedCourse       = $request->course ?? null;
        $selectedYear         = $request->year ?? null;
        $selectedSection      = $request->section ?? null;
        $selectedAdviser      = $request->adviser ?? null;
        $selectedStatus       = $request->status ?? null;
        $selectedOrganization = $request->organization ?? null;

        $students = $this->getStudents(
            $collegeId,
            $selectedSY,
            $selectedSem,
            $selectedCourse,
            $selectedYear,
            $selectedSection,
            $selectedAdviser,
            $selectedStatus
        );

        $payments = $this->getPayments($selectedSY, $selectedSem);

        $selectedSemId = Semester::where('name', $selectedSem)->value('id'); // returns null if not found

$feesQuery = \App\Models\Fee::with('organization')
    ->where('status', 'approved')
    ->where(function($q) use ($selectedSY, $selectedSemId) {
        $q->whereNull('created_school_year_id')
          ->orWhere('created_school_year_id', '<=', $selectedSY);

        if ($selectedSemId) {
            $q->where(function($q2) use ($selectedSemId) {
                $q2->whereNull('created_semester_id')
                   ->orWhere('created_semester_id', '<=', $selectedSemId);
            });
        } else {
            // If no semester selected, just ignore the semester condition
            $q->whereNull('created_semester_id');
        }
    });
        if ($selectedOrganization) {
            if ($selectedOrganization === 'college_only') {
                $feesQuery->where('fee_scope', 'college')->whereNull('organization_id');
            } else {
                $feesQuery->where('organization_id', $selectedOrganization);
            }
        }
        $fees = $feesQuery->orderBy('created_at', 'desc')->get();

        $college = auth()->user()->college;

        $paidFees = $payments->flatMap(fn($p) => $p->fees)
            ->filter(fn($f) => $f->pivot->amount_paid > 0);

        $unpaidFees = $payments->flatMap(fn($p) => $p->fees)
            ->filter(fn($f) => $f->pivot->amount_paid == 0);

        $totalPayments = $paidFees->count();
        $totalUnpaid   = $unpaidFees->count();
        $totalAmount   = $paidFees->sum(fn($f) => $f->pivot->amount_paid);

        $requirementBreakdown = $paidFees
            ->groupBy('requirement_level')
            ->map(fn($fees) => $fees->sum(fn($f) => $f->pivot->amount_paid));

        return view('college.history', compact(
            'students',
            'payments',
            'schoolYears',
            'semesters',
            'selectedSY',
            'selectedSem',
            'selectedSchoolYear',
            'selectedSemester',
            'courses',
            'years',
            'sections',
            'advisers',
            'selectedCourse',
            'selectedYear',
            'selectedSection',
            'selectedAdviser',
            'selectedStatus',
            'organizations',
            'fees',
            'selectedOrganization',
            'college',
            'totalPayments',
            'totalUnpaid',
            'totalAmount',
            'requirementBreakdown'
        ));
    }

    private function getActiveSchoolYearAndSemester(Request $request): array
    {
        $activeSY  = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $selectedSY  = $request->school_year ?? $activeSY?->id;
        $selectedSem = $request->semester ?? $activeSem?->name;

        $selectedSchoolYear = SchoolYear::find($selectedSY);
        $selectedSemester   = Semester::where('name', $selectedSem)->first();

        return [
            'selectedSY' => $selectedSY,
            'selectedSem' => $selectedSem,
            'selectedSchoolYear' => $selectedSchoolYear,
            'selectedSemester' => $selectedSemester,
        ];
    }

    private function getStudents(
        int $collegeId,
        ?int $selectedSY,
        ?string $selectedSem,
        ?int $selectedCourse,
        ?int $selectedYear,
        ?int $selectedSection,
        ?int $selectedAdviser,
        ?string $selectedStatus
    ) {
        $search = request('search');

        return StudentEnrollment::with(['student', 'course', 'yearLevel', 'section', 'schoolYear', 'semester', 'adviser'])
            ->join('students', 'student_enrollments.student_id', '=', 'students.id')
            ->where('student_enrollments.college_id', $collegeId)
            ->when($selectedSY, fn($q) => $q->where('student_enrollments.school_year_id', $selectedSY))
            ->when($selectedSem, fn($q) => $q->whereHas('semester', fn($s) => $s->where('name', $selectedSem)))
            ->when($selectedCourse, fn($q) => $q->where('student_enrollments.course_id', $selectedCourse))
            ->when($selectedYear, fn($q) => $q->where('student_enrollments.year_level_id', $selectedYear))
            ->when($selectedSection, fn($q) => $q->where('student_enrollments.section_id', $selectedSection))
            ->when($selectedAdviser, fn($q) => $q->where('student_enrollments.adviser_id', $selectedAdviser))
            ->when($selectedStatus, function ($q) use ($selectedStatus) {
                match ($selectedStatus) {
                    'assessed' => $q->whereNotNull('assessed_at'),
                    'to_assess' => $q->whereNull('assessed_at')->whereNotNull('validated_at'),
                    'pending_payment' => $q->whereNull('validated_at')->whereNotNull('advised_at'),
                    'not_enrolled' => $q->whereNull('advised_at'),
                    default => null
                };
            })
            ->when($search, fn($q) => $q->where('students.first_name', 'like', "%$search%")
                ->orWhere('students.last_name', 'like', "%$search%")
                ->orWhere('students.student_id', 'like', "%$search%"))
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->select('student_enrollments.*')
            ->get();
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
        $collegeId   = auth()->user()->college_id;

        $fees = \App\Models\Fee::with('organization')
            ->where('status', 'approved')
            ->where(function ($q) use ($collegeId) {
                $q->where('fee_scope', 'university-wide')
                    ->orWhere('fee_scope', 'college')
                    ->orWhereHas('organization', fn($org) => $org->where('college_id', $collegeId));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $payments = Payment::with('fees')
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('transaction_id');

        return view('college.student-history-info', compact('studentEnrollments', 'studentInfo', 'fees', 'payments'));
    }

    private function getPayments(?int $selectedSY, ?string $selectedSem)
    {
        $collegeId = auth()->user()->college_id;
        $organizationId   = request('organization');
        $feeId            = request('fee');
        $requirementLevel = request('requirement_level');
        $recurrence       = request('recurrence');
        $paymentStatus    = request('payment_status');
        $fromDate         = request('from_date');
        $toDate           = request('to_date');

        $students = $this->getStudents(
            $collegeId,
            $selectedSY,
            $selectedSem,
            request('course'),
            request('year'),
            request('section'),
            request('adviser'),
            request('status')
        );

        $selectedSemId = Semester::where('name', $selectedSem)->value('id'); // returns null if not found

$feesQuery = \App\Models\Fee::with('organization')
    ->where('status', 'approved')
    ->where(function($q) use ($selectedSY, $selectedSemId) {
        $q->whereNull('created_school_year_id')
          ->orWhere('created_school_year_id', '<=', $selectedSY);

        if ($selectedSemId) {
            $q->where(function($q2) use ($selectedSemId) {
                $q2->whereNull('created_semester_id')
                   ->orWhere('created_semester_id', '<=', $selectedSemId);
            });
        } else {
            // If no semester selected, just ignore the semester condition
            $q->whereNull('created_semester_id');
        }
    });
        if ($organizationId) {
            if ($organizationId === 'college_only') {
                $feesQuery->where('fee_scope', 'college')->whereNull('organization_id');
            } else {
                $feesQuery->where('organization_id', $organizationId);
            }
        }

        if ($feeId) {
            $feesQuery->where('id', $feeId);
        }

        if ($requirementLevel) {
            $feesQuery->where('requirement_level', $requirementLevel);
        }

        if ($recurrence) {
            $feesQuery->where('recurrence', $recurrence);
        }

        $fees = $feesQuery->get();

        $payments = collect();

        foreach ($students as $student) {
            foreach ($fees as $fee) {
                $payment = Payment::with('student')
                    ->where('student_id', $student->student_id)
                    ->where('school_year_id', $selectedSY)
                    ->when($selectedSem, fn($q) => $q->whereHas('semester', fn($s) => $s->where('name', $selectedSem)))
                    ->when($organizationId, function ($q) use ($organizationId) {
                        if ($organizationId === 'college_only') {
                            $q->whereNull('organization_id');
                        } else {
                            $q->where('organization_id', $organizationId);
                        }
                    })
                    ->whereHas('fees', fn($f) => $f->where('fees.id', $fee->id))
                    ->first();

                if ($paymentStatus) {
                    $isPaid = $payment?->fees->first()?->pivot->amount_paid > 0;
                    if (($paymentStatus === 'paid' && !$isPaid) || ($paymentStatus === 'unpaid' && $isPaid)) {
                        continue;
                    }
                }

                if (($fromDate || $toDate) && $payment?->created_at) {
                    $createdAt = $payment->created_at->format('Y-m-d');
                    if ($fromDate && $createdAt < $fromDate) continue;
                    if ($toDate && $createdAt > $toDate) continue;
                } elseif (($fromDate || $toDate) && !$payment?->created_at) {
                    continue;
                }

                $feeData = clone $fee;
                $feeData->pivot = (object) [
                    'amount_paid' => $payment?->fees->first()?->pivot->amount_paid ?? 0,
                ];

                $payments->push((object)[
                    'student'    => $student->student,
                    'fees'       => [$feeData],
                    'created_at' => $payment?->created_at,
                ]);
            }
        }

        return $payments->sortByDesc(fn($p) => $p->created_at)->values();
    }

    public function getFeesByOrg(Request $request)
    {
        $collegeId = Auth::user()->college_id;
        $orgId = $request->organization;

        $feesQuery = \App\Models\Fee::with('organization')->where('status', 'approved');

        if ($orgId === 'college_only') {
            $feesQuery->where('fee_scope', 'college')->whereNull('organization_id');
        } elseif ($orgId) {
            $feesQuery->where('organization_id', $orgId);
        }

        $fees = $feesQuery->orderBy('created_at', 'desc')->get(['id', 'fee_name']);

        return response()->json($fees);
    }


    private function generatePdfReport($data, $tab, $selectedSchoolYear, $selectedSem)
    {
        $pdf = Pdf::loadView('college.reports.history-pdf', compact(
            'data',
            'tab',
            'selectedSchoolYear',
            'selectedSem'
        ));

        return $pdf->download("{$tab}-report.pdf");
    }
    public function generateReport(Request $request)
    {
        $format = $request->format;
        $tab    = $request->tab ?? 'enrollments';

        $activeSelection = $this->getActiveSchoolYearAndSemester($request);
        extract($activeSelection);

        if ($tab === 'payments') {
            $data = $this->getPayments($selectedSY, $selectedSem);
        } else {
            $data = $this->getStudents(
                auth()->user()->college_id,
                $selectedSY,
                $selectedSem,
                $request->course,
                $request->year,
                $request->section,
                $request->adviser,
                $request->status
            );
        }

        if ($format === 'pdf') {
            return $this->generatePdfReport($data, $tab, $selectedSchoolYear, $selectedSem);
        }

        if ($format === 'excel') {
            return $this->generateExcelReport($data, $tab);
        }

        abort(404);
    }
}
