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

        $totalStudentsPaid = Student::whereHas('payments', function ($q) use ($childOrgs, $activeSY, $activeSem) {
            $q->whereIn('organization_id', $childOrgs->pluck('id'))
                ->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id);
        })->count();

        $totalPaymentsCollected = Payment::whereIn('organization_id', $childOrgs->pluck('id'))
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->sum('amount_due');

        $pendingStudents = $totalStudents - $totalStudentsPaid;

        /*
        PAYMENT TREND
        */

        $paymentsPerDay = Payment::whereIn('organization_id', $childOrgs->pluck('id'))
            ->where('school_year_id', $activeSY->id)
            ->where('semester_id', $activeSem->id)
            ->selectRaw('DATE(created_at) as date, SUM(amount_due) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyPaymentLabels = $paymentsPerDay->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('M d'));

        $dailyPaymentData = $paymentsPerDay->pluck('total');

        /*
        CHILD ORG PERFORMANCE
        */

        $childOrgPerformance = $childOrgs->map(function ($org) use ($activeSY, $activeSem) {

            $students = Student::whereHas('enrollments', function ($q) use ($org, $activeSY, $activeSem) {
                $q->where('college_id', $org->college_id)
                    ->where('school_year_id', $activeSY->id)
                    ->where('semester_id', $activeSem->id)
                    ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
            })->count();

            $paidStudents = Payment::where('organization_id', $org->id)
                ->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id)
                ->distinct('student_id')
                ->count('student_id');

            $payments = Payment::where('organization_id', $org->id)
                ->where('school_year_id', $activeSY->id)
                ->where('semester_id', $activeSem->id)
                ->sum('amount_due');

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

                $paid = Payment::whereIn('organization_id', $childOrgs->pluck('id'))
                    ->where('school_year_id', $activeSY->id)
                    ->where('semester_id', $activeSem->id)
                    ->whereHas('fees', fn($q) => $q->where('fee_id', $fee->id))
                    ->count();

                return [
                    'name' => $fee->fee_name,
                    'amount' => $fee->amount,
                    'paid' => $paid,
                ];
            });

        /*
        RECENT TRANSACTIONS
        */

        $recentPayments = Payment::with('student')
            ->whereIn('organization_id', $childOrgs->pluck('id'))
            ->latest()
            ->take(5)
            ->get();

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