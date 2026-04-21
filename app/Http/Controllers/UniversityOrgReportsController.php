<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class UniversityOrgReportsController extends Controller
{
    public function paymentCollectionReport(Request $request)
    {
        $user = Auth::user();
        $motherOrg = $user?->organization;

        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters = Semester::orderBy('id')->get();

        $selectedSY = $request->input('school_year_id')
            ? SchoolYear::find($request->school_year_id)
            : SchoolYear::where('is_active', true)->first();

        $selectedSY = $selectedSY ?? $schoolYears->first();

        $selectedSem = $request->input('semester_id')
            ? Semester::find($request->semester_id)
            : Semester::where('is_active', true)->first();

        $selectedSem = $selectedSem ?? $semesters->first();

        $selectedSYId = $selectedSY?->id;
        $selectedSemId = $selectedSem?->id;

        $motherOrgFeeIds = [];
        $childOrgs = collect();
        $recentOrgs = collect();
        $totalChildOrgs = 0;
        $totalStudentsPaid = 0;
        $totalPaymentsCollected = 0;
        $dailyPaymentLabels = [];
        $dailyPaymentData = [];
        $officeCollectionLabels = [];
        $officeCollectionData = [];
        $totalPendingStudents = 0;

        if ($motherOrg && $motherOrg->role === 'university_org' && $selectedSYId && $selectedSemId) {
            $childOrgs = $motherOrg->childOrganizations()
                ->with(['orgAdmin', 'college'])
                ->get();

            // fees that belong to the mother org (and are valid for the selected school year/semester)
            $motherOrgFees = Fee::where('organization_id', $motherOrg->id)
                ->where(function ($q) use ($selectedSY, $selectedSem) {
                    $q->where('created_school_year_id', '<', $selectedSY->id)
                        ->orWhere(function ($q2) use ($selectedSY, $selectedSem) {
                            $q2->where('created_school_year_id', $selectedSY->id)
                                ->where('created_semester_id', '<=', $selectedSem->id);
                        });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $motherOrgFeeIds = $motherOrgFees->pluck('id')->toArray();

            $recentOrgs = $motherOrg->childOrganizations()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($org) use ($selectedSY, $selectedSem, $motherOrgFeeIds) {

                    $org->total_students = Student::whereHas('enrollments', function ($q) use ($org, $selectedSY, $selectedSem) {
                        $q->where('college_id', $org->college_id)
                            ->where('school_year_id', $selectedSY->id)
                            ->where('semester_id', $selectedSem->id)
                            ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
                    })->count();

                    $org->total_payments_collected = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                        ->where('payments.organization_id', $org->id)
                        ->where('payments.school_year_id', $selectedSY->id)
                        ->where('payments.semester_id', $selectedSem->id)
                        ->whereIn('fee_payment.fee_id', $motherOrgFeeIds)
                        ->sum('fee_payment.amount_paid');

                    return $org;
                });

            $childOrgs->each(function ($org) use ($selectedSY, $selectedSem, $motherOrgFees, $motherOrgFeeIds) {
                $org->amount_collected = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('payments.organization_id', $org->id)
                    ->where('payments.school_year_id', $selectedSY->id)
                    ->where('payments.semester_id', $selectedSem->id)
                    ->whereIn('fee_payment.fee_id', $motherOrgFeeIds)
                    ->sum('fee_payment.amount_paid');

                $orgFees = $motherOrgFees->map(function ($fee) use ($org, $selectedSY, $selectedSem) {
                    $feeClone = clone $fee;

                    $students = Student::whereHas('enrollments', function ($q) use ($org, $selectedSY, $selectedSem) {
                        $q->where('college_id', $org->college_id)
                            ->where('school_year_id', $selectedSY->id)
                            ->where('semester_id', $selectedSem->id)
                            ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
                    })->get();

                    $paidStudentIds = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                        ->where('payments.organization_id', $org->id)
                        ->where('payments.school_year_id', $selectedSY->id)
                        ->where('payments.semester_id', $selectedSem->id)
                        ->where('fee_payment.fee_id', $fee->id)
                        ->pluck('student_id')
                        ->unique()
                        ->toArray();

                    $feeClone->paid_students = $students->whereIn('id', $paidStudentIds);
                    $feeClone->pending_students = $students->whereNotIn('id', $paidStudentIds);

                    return $feeClone;
                });

                $org->setRelation('fees', $orgFees);
            });
        } else {
            $childOrgs = collect();
        }

        $totalChildOrgs = $childOrgs->count();

        $orgIds = $childOrgs->pluck('id');

        $totalStudentsPaid = 0;
        $totalPaymentsCollected = 0;
        $paymentsPerDay = collect();
        $officeCollections = collect();

        if ($selectedSYId && $selectedSemId && count($motherOrgFeeIds)) {
            $totalStudentsPaid = Student::whereHas('payments', function ($q) use ($orgIds, $motherOrgFeeIds, $selectedSYId, $selectedSemId) {
                $q->whereIn('organization_id', $orgIds)
                    ->where('school_year_id', $selectedSYId)
                    ->where('semester_id', $selectedSemId)
                    ->whereHas('fees', fn($q2) => $q2->whereIn('fees.id', $motherOrgFeeIds));
            })->count();

            $totalPaymentsCollected = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                ->whereIn('payments.organization_id', $orgIds)
                ->where('payments.school_year_id', $selectedSYId)
                ->where('payments.semester_id', $selectedSemId)
                ->whereIn('fee_payment.fee_id', $motherOrgFeeIds)
                ->sum('fee_payment.amount_paid');

            $paymentsPerDay = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                ->whereIn('payments.organization_id', $orgIds)
                ->where('payments.school_year_id', $selectedSYId)
                ->where('payments.semester_id', $selectedSemId)
                ->whereIn('fee_payment.fee_id', $motherOrgFeeIds)
                ->selectRaw('DATE(payments.created_at) as date, SUM(fee_payment.amount_paid) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $officeCollections = $childOrgs->map(function ($org) use ($selectedSYId, $selectedSemId, $motherOrgFeeIds) {
                $total = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                    ->where('payments.organization_id', $org->id)
                    ->where('payments.school_year_id', $selectedSYId)
                    ->where('payments.semester_id', $selectedSemId)
                    ->whereIn('fee_payment.fee_id', $motherOrgFeeIds)
                    ->sum('fee_payment.amount_paid');

                return [
                    'name' => $org->name,
                    'total' => $total,
                ];
            });
        }

        $officeCollectionLabels = $officeCollections->pluck('name')->toArray();
        $officeCollectionData = $officeCollections->pluck('total')->toArray();
        // Sum pending student counts across all fees (as computed per-org above)
        $totalPendingStudents = $childOrgs->sum(function ($org) {
            return $org->fees->sum(fn($fee) => $fee->pending_students->count());
        });

        return view('university_org.reports', compact(
            'motherOrg',
            'childOrgs',
            'recentOrgs',
            'schoolYears',
            'semesters',
            'selectedSY',
            'selectedSem',
            'totalChildOrgs',
            'totalStudentsPaid',
            'totalPaymentsCollected',
            'totalPendingStudents',
            'dailyPaymentLabels',
            'dailyPaymentData',
            'officeCollectionLabels',
            'officeCollectionData'
        ));
    }


    public function childOrgFees(Request $request)
    {
        $user = Auth::user();
        $motherOrg = $user?->organization;

        if (!$motherOrg || $motherOrg->role !== 'university_org') {
            abort(403);
        }
        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters = Semester::orderBy('id')->get();
        $org = $motherOrg->childOrganizations()->findOrFail($request->org_id);

        $selectedSY = $request->input('school_year_id')
            ? SchoolYear::find($request->school_year_id)
            : SchoolYear::where('is_active', true)->first();

        $selectedSem = $request->input('semester_id')
            ? Semester::find($request->semester_id)
            : Semester::where('is_active', true)->first();

        $selectedSYId = $selectedSY?->id;
        $selectedSemId = $selectedSem?->id;

        if (!$selectedSYId || !$selectedSemId) {
            $fees = collect();
        } else {
            $fees = Fee::where('organization_id', $motherOrg->id)
            ->where(function ($q) use ($selectedSY, $selectedSem) {
                $q->where('created_school_year_id', '<', $selectedSY->id)
                    ->orWhere(function ($q2) use ($selectedSY, $selectedSem) {
                        $q2->where('created_school_year_id', $selectedSY->id)
                            ->where('created_semester_id', '<=', $selectedSem->id);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
        }

        $fees->each(function ($fee) use ($org, $selectedSY, $selectedSem) {
            $students = Student::whereHas('enrollments', function ($q) use ($org, $selectedSY, $selectedSem) {
                $q->where('college_id', $org->college_id)
                    ->where('school_year_id', $selectedSY->id)
                    ->where('semester_id', $selectedSem->id)
                    ->whereIn('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED']);
            })->get();

            $paidStudentIds = Payment::join('fee_payment', 'payments.id', '=', 'fee_payment.payment_id')
                ->where('payments.organization_id', $org->id)
                ->where('payments.school_year_id', $selectedSY->id)
                ->where('payments.semester_id', $selectedSem->id)
                ->where('fee_payment.fee_id', $fee->id)
                ->pluck('student_id')
                ->unique()
                ->toArray();

            $fee->paid_students = $students->whereIn('id', $paidStudentIds);
            $fee->pending_students = $students->whereNotIn('id', $paidStudentIds);
        });

        return view('university_org.child_org_fees', compact(
            'org',
            'fees',
            'selectedSY',
            'selectedSem',
            'schoolYears',
            'semesters'
        ));
    }

}
