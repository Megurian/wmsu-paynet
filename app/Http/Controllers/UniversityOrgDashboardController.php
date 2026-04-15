<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\Semester;
use Carbon\Carbon;

class UniversityOrgDashboardController extends Controller
{
    public function index()
    {
        $motherOrg = Auth::user()->organization;

        if (!$motherOrg || $motherOrg->role !== 'university_org') {
            abort(403);
        }

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $childOrgs = $motherOrg->childOrganizations()->get();

        $feeIds = Fee::where('organization_id', $motherOrg->id)
            ->where('status', 'approved')
            ->pluck('id')
            ->toArray();

        /*
        SUMMARY METRICS
        */

        $totalChildOrgs = $childOrgs->count();

        $totalStudents = Student::whereHas('enrollments', function ($q) use ($childOrgs, $activeSY, $activeSem) {
            $q->whereIn('college_id', $childOrgs->pluck('college_id'))
                ->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id)
                ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
        })->count();

        $totalStudentsPaid = Student::whereHas('payments', function ($q) use ($childOrgs, $activeSY, $activeSem, $feeIds) {
            $q->whereIn('organization_id', $childOrgs->pluck('id'))
                ->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id)
                ->when(count($feeIds), fn($q) => $q->whereHas('fees', fn($q2) => $q2->whereIn('fees.id', $feeIds)));
        })->count();

        $totalPaymentsCollected = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
            ->whereIn('payments.organization_id', $childOrgs->pluck('id'))
            ->where('payments.school_year_id', $activeSY->id)
            ->where('payments.semester_id', $activeSem->id)
            ->when(count($feeIds), fn($q) => $q->whereIn('fee_payment.fee_id', $feeIds))
            ->sum('fee_payment.amount_paid');

        $pendingStudents = $totalStudents - $totalStudentsPaid;

        /*
        PAYMENT TREND
        */

        $paymentsPerDay = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
            ->whereIn('payments.organization_id', $childOrgs->pluck('id'))
            ->where('payments.school_year_id', $activeSY->id)
            ->where('payments.semester_id', $activeSem->id)
            ->when(count($feeIds), fn($q) => $q->whereIn('fee_payment.fee_id', $feeIds))
            ->selectRaw('DATE(payments.created_at) as date, SUM(fee_payment.amount_paid) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyPaymentLabels = $paymentsPerDay->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('M d'));

        $dailyPaymentData = $paymentsPerDay->pluck('total');

        /*
        CHILD ORG PERFORMANCE
        */

        $childOrgPerformance = $childOrgs->map(function ($org) use ($activeSY, $activeSem, $feeIds) {

            $students = Student::whereHas('enrollments', function ($q) use ($org, $activeSY, $activeSem) {
                $q->where('college_id', $org->college_id)
                    ->where('school_year_id', $activeSY->id)
                    ->where('semester_id', $activeSem->id)
                    ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
            })->count();

            $paidStudents = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('payments.organization_id', $org->id)
                ->where('payments.school_year_id', $activeSY->id)
                ->where('payments.semester_id', $activeSem->id)
                ->when(count($feeIds), fn($q) => $q->whereIn('fee_payment.fee_id', $feeIds))
                ->distinct()
                ->count('payments.student_id');

            $payments = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('payments.organization_id', $org->id)
                ->where('payments.school_year_id', $activeSY->id)
                ->where('payments.semester_id', $activeSem->id)
                ->when(count($feeIds), fn($q) => $q->whereIn('fee_payment.fee_id', $feeIds))
                ->sum('fee_payment.amount_paid');

            return [
                'name' => $org->name,
                'students' => $students,
                'paid' => $paidStudents,
                'pending' => $students - $paidStudents,
                'payments' => $payments,
            ];
        });

        /*
        FEE COLLECTION PROGRESS
        */

        $fees = Fee::where('organization_id', $motherOrg->id)
            ->where('status', 'approved')
            ->get()
            ->map(function ($fee) use ($childOrgs, $activeSY, $activeSem) {

                $paid = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                    ->whereIn('payments.organization_id', $childOrgs->pluck('id'))
                    ->where('payments.school_year_id', $activeSY->id)
                    ->where('payments.semester_id', $activeSem->id)
                    ->where('fee_payment.fee_id', $fee->id)
                    ->distinct()
                    ->count('payments.student_id');

                $expected = Student::whereHas('enrollments', function ($q) use ($childOrgs, $activeSY, $activeSem) {
                    $q->whereIn('college_id', $childOrgs->pluck('college_id'))
                        ->where('school_year_id', $activeSY->id)
                        ->where('semester_id', $activeSem->id)
                        ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
                })->count();

                $percent = $expected ? round($paid / $expected * 100) : 0;

                return [
                    'name' => $fee->fee_name,
                    'amount' => $fee->amount,
                    'paid' => $paid,
                    'expected' => $expected,
                    'percent' => min(100, $percent),
                ];
            });

        /*
        RECENT TRANSACTIONS
        */

        $recentPayments = Payment::with(['student', 'fees' => function ($q) use ($feeIds) {
                if (count($feeIds)) {
                    $q->whereIn('fees.id', $feeIds);
                }
            }])
            ->whereIn('organization_id', $childOrgs->pluck('id'))
            ->when(count($feeIds), fn($q) => $q->whereHas('fees', fn($q2) => $q2->whereIn('fees.id', $feeIds)))
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($payment) use ($feeIds) {
                $payment->amount_paid = $payment->fees->sum(fn($f) => $f->pivot->amount_paid);
                return $payment;
            });

        return view('university_org.dashboard', compact(
            'motherOrg',
            'totalChildOrgs',
            'totalStudents',
            'totalStudentsPaid',
            'pendingStudents',
            'totalPaymentsCollected',
            'dailyPaymentLabels',
            'dailyPaymentData',
            'childOrgPerformance',
            'fees',
            'recentPayments'
        ));
    }
}