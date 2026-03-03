<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Fee;
use App\Models\Organization;
use App\Models\StudentEnrollment;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

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

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $students = Student::whereHas('enrollments', function ($q) use ($activeSY, $activeSem, $collegeId) {
                $q->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED'])
                    ->where('school_year_id', $activeSY->id)
                    ->where('semester_id', $activeSem->id)
                    ->where('college_id', $collegeId);
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

    public function getStudentFees($studentId)
    {
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $student = Student::with(['enrollments' => function ($q) use ($activeSY, $activeSem) {
            $q->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id)
                ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
        }, 'enrollments.course', 'enrollments.yearLevel', 'enrollments.section'])
            ->findOrFail($studentId);

        $activeEnrollment = $student->enrollments->first();

        if (!$activeEnrollment) {
            return response()->json(['message' => 'Student not enrolled.'], 404);
        }
        $userOrg = auth()->user()->organization;

        $organizationIds = [$userOrg->id];

        // Only children (orgs that have a mother_organization_id) may inherit their mother's fees.
        if ($userOrg->mother_organization_id) {
            $organizationIds[] = $userOrg->mother_organization_id;
        }

        // Build base query: include fees belonging to this organization and — only when allowed — university-wide fees.
        $feesQuery = Fee::where('status', 'approved')
            ->where(function($q) use ($organizationIds, $userOrg) {
                $q->whereIn('organization_id', $organizationIds);

                // Special exception: only children of mother orgs that inherit OSA fees may see fees created by OSA
                if ($userOrg->motherOrganization?->inherits_osa_fees) {
                    $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
                    if ($osaId) {
                        $q->orWhere('organization_id', $osaId);
                    }
                }
            })
            ->orderBy('created_at', 'desc');

        // Ensure uniqueness (in case a fee matches multiple conditions)
        $fees = $feesQuery->get()->unique('id')->values();

        $paidFeeIds = DB::table('fee_payment')
            ->join('payments', 'fee_payment.payment_id', '=', 'payments.id')
            ->where('payments.enrollment_id', $activeEnrollment->id)
            ->pluck('fee_payment.fee_id')
            ->toArray();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'course' => $activeEnrollment?->course?->name,
                'year' => $activeEnrollment?->yearLevel?->name,
                'section' => $activeEnrollment?->section?->name,
            ],
            'fees' => $fees,
            'paid_fee_ids' => $paidFeeIds  
        ]);
    }


    public function collectPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required|array|min:1',
            'fee_ids.*' => 'exists:fees,id',
            'cash_received' => 'required|numeric|min:0',
        ]);

        // Authenticate organization and get active periods
        $user = auth()->user();
        $organization = $user->organization;

        if (!$organization) {
            return response()->json(['message' => 'Organization not found.'], 422);
        }

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        if (!$activeSY || !$activeSem) {
            return response()->json(['message' => 'No active school year or semester.'], 422);
        }

        // Fetch student
        $student = Student::findOrFail($request->student_id);

        // Get student's active enrollment
        $enrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->firstOrFail();

        // Verify student belongs to the correct college
        if ($enrollment->college_id !== $organization->college_id) {
            return response()->json(['message' => 'Student does not belong to this organization.'], 403);
        }

        if (!in_array($enrollment->status, [StudentEnrollment::FOR_PAYMENT_VALIDATION, StudentEnrollment::ENROLLED])) {
            return response()->json([
                'message' => "Student enrollment status '{$enrollment->status}' does not allow payment."
            ], 422);
        }

        $fees = Fee::whereIn('id', $request->fee_ids)->get();

        if ($fees->count() !== count($request->fee_ids)) {
            return response()->json(['message' => 'One or more fees do not exist.'], 422);
        }

        // Verify all fees belong to this organization or are inherited from mother org
        $organizationIds = [$organization->id];
        if ($organization->mother_organization_id) {
            $organizationIds[] = $organization->mother_organization_id;
        }

        // Special exception: allow OSA fees if mother org inherits them
        if ($organization->motherOrganization?->inherits_osa_fees) {
            $osaId = Organization::where('org_code', 'OSA')->value('id');
            if ($osaId) {
                $organizationIds[] = $osaId;
            }
        }

        $invalidFees = $fees->whereNotIn('organization_id', $organizationIds)->pluck('id')->toArray();
        if (!empty($invalidFees)) {
            return response()->json([
                'message' => 'One or more selected fees do not belong to your organization.'
            ], 403);
        }

        $totalAmount = $fees->sum('amount');

        if ($request->cash_received < $totalAmount) {
            return response()->json(['message' => 'Cash received is less than total amount due.'], 422);
        }

        $change = $request->cash_received - $totalAmount;

        // Check for already-paid fees
        $alreadyPaid = DB::table('fee_payment')
            ->join('payments', 'fee_payment.payment_id', '=', 'payments.id')
            ->where('payments.enrollment_id', $enrollment->id)
            ->whereIn('fee_payment.fee_id', $request->fee_ids)
            ->pluck('fee_payment.fee_id')
            ->toArray();

        if (!empty($alreadyPaid)) {
            $dupeCount = count($alreadyPaid);
            return response()->json([
                'message' => "$dupeCount fee(s) already paid for this enrollment."
            ], 422);
        }

        $orgCode = $organization->org_code ?? 'GEN';
        $dateStr = now()->format('Ymd');
        
        $countToday = Payment::where('organization_id', $organization->id)
            ->whereDate('created_at', now())
            ->count();
        
        $sequenceNum = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
        $randomSuffix = strtoupper(str_pad(dechex(random_int(0, 4095)), 3, '0', STR_PAD_LEFT));
        
        $transactionId = "{$orgCode}-{$dateStr}-{$sequenceNum}-{$randomSuffix}";

        $payment = Payment::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'organization_id' => $organization->id,
            'school_year_id' => $activeSY->id,
            'semester_id' => $activeSem->id,
            'amount_due' => $totalAmount,
            'cash_received' => $request->cash_received,
            'change' => $change,
            'collected_by' => auth()->id(),
            'transaction_id' => $transactionId,
        ]);

        foreach ($fees as $fee) {
            $payment->fees()->attach($fee->id, ['amount_paid' => $fee->amount]);
        }

        return response()->json([
            'message' => 'Payment collected successfully.',
            'payment_id' => $payment->id,
            'transaction_id' => $transactionId,
            'amount_due' => $totalAmount,
            'change' => $change,
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => "{$student->first_name} {$student->last_name}"
            ]
        ]);
    }

    // public function downloadReceipt(Payment $payment)
    // {
    //     $payment->load([
    //         'student',
    //         'fees',
    //         'enrollment.course',
    //         'enrollment.yearLevel',
    //         'enrollment.section',
    //         'collector'
    //     ]);
    //
    //     $html = view('college_org.receipt_pdf', compact('payment'))->render();
    //
    //     $mpdf = new Mpdf([
    //         'mode' => 'utf-8',
    //         'format' => 'A4',
    //     ]);
    //
    //     $mpdf->WriteHTML($html);
    //
    //     if (!$payment->fees->contains('organization_id', auth()->user()->organization->id)) {
    //         abort(403);
    //     }
    //
    //     return response(
    //         $mpdf->Output('receipt-' . $payment->transaction_id . '.pdf', 'S'),
    //         200,
    //         [
    //             'Content-Type' => 'application/pdf',
    //             'Content-Disposition' => 'inline; filename="receipt-' . $payment->transaction_id . '.pdf"'
    //         ]
    //     );
    // }

public function records(Request $request)
{
    $user = auth()->user();
    $organization = $user->organization;
    $organizationId = $organization->id;
    $collegeId = $organization->college_id;

    // Get active S.Y. and Sem
$activeSYId = SchoolYear::where('is_active', true)->value('id');
$activeSemId = Semester::where('is_active', true)->value('id');

// Use request input or default to active
$schoolYearId = $request->input('school_year_id', $activeSYId);
$semesterId = $request->input('semester_id', $activeSemId);

$paymentsQuery = Payment::with([
    'student',
    'fees',
    'enrollment.course',
    'enrollment.yearLevel',
    'enrollment.section'
])
->where('organization_id', $organizationId)
->where('school_year_id', $schoolYearId)
->where('semester_id', $semesterId);

    // Filter by Fee
    if ($request->filled('fee_id')) {
        $paymentsQuery->whereHas('fees', function($q) use ($request) {
            $q->where('fees.id', $request->fee_id);
        });
    }

    // Filter by Fee Recurrence
    if ($request->filled('fee_recurrence')) {
        $paymentsQuery->whereHas('fees', function($q) use ($request) {
            $q->where('recurrence', $request->fee_recurrence);
        });
    }

    // Filter by Requirement Level
    if ($request->filled('requirement_level')) {
        $paymentsQuery->whereHas('fees', function($q) use ($request) {
            $q->where('requirement_level', $request->requirement_level);
        });
    }

    // Filter by Date Range
    if ($request->filled('date_from')) {
        $paymentsQuery->whereDate('created_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $paymentsQuery->whereDate('created_at', '<=', $request->date_to);
    }

    // Filter by Course
    if ($request->filled('course_id')) {
        $paymentsQuery->whereHas('enrollment.course', function($q) use ($request) {
            $q->where('id', $request->course_id);
        });
    }

    // Filter by Year Level
    if ($request->filled('year_level_id')) {
        $paymentsQuery->whereHas('enrollment.yearLevel', function($q) use ($request) {
            $q->where('id', $request->year_level_id);
        });
    }

    // Filter by Section
    if ($request->filled('section_id')) {
        $paymentsQuery->whereHas('enrollment.section', function($q) use ($request) {
            $q->where('id', $request->section_id);
        });
    }

    $payments = $paymentsQuery->latest()->get();

    // Filter options for dropdowns

    $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
    $semesters = Semester::orderBy('id')->get();

   // Determine organizations whose fees should appear
$organizationIds = [$organization->id];
if ($organization->mother_organization_id) {
    $organizationIds[] = $organization->mother_organization_id;
}

// Include OSA fees if mother org inherits them
if ($organization->motherOrganization?->inherits_osa_fees) {
    $osaId = \App\Models\Organization::where('org_code', 'OSA')->value('id');
    if ($osaId) {
        $organizationIds[] = $osaId;
    }
}

// Fetch fees for dropdown
$fees = Fee::where('status', 'approved')
    ->whereIn('organization_id', $organizationIds)
    ->orderBy('created_at', 'desc')
    ->get();

    // Only courses, years, and sections under this college
    $courses = \App\Models\Course::where('college_id', $collegeId)->get();
    $yearLevels = \App\Models\YearLevel::where('college_id', $collegeId)->get();
    $sections = \App\Models\Section::where('college_id', $collegeId)->get();

    return view('college_org.records', compact(
        'payments',
        'schoolYears',
        'semesters',
        'fees',
        'courses',
        'yearLevels',
        'sections',
        'schoolYearId',
        'semesterId'
    ));
}
 }
