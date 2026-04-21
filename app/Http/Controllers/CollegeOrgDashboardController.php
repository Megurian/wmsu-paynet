<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Student;

class CollegeOrgDashboardController extends Controller
{
    public function dashboard()
    {
       $user = Auth::user();
        abort_unless($user && in_array('college_org', (array) $user->role), 403);

        $collegeId = $user->college_id;
        $orgId = $user->organization_id;

        $org = Organization::where('id', $orgId)
            ->where('college_id', $collegeId)
            ->firstOrFail();

        // Payments for THIS college org only
        $payments = Payment::where('organization_id', $orgId)->get();

        $totalPaymentsCollected = $payments->sum('amount_due');

        $totalStudentsPaid = Payment::where('organization_id', $orgId)
            ->distinct('student_id')
            ->count('student_id');

        $pendingStudents = Student::whereHas('enrollments', function ($q) use ($collegeId) {
            $q->where('college_id', $collegeId)
                ->where('status', 'ENROLLED');
        })->count() - $totalStudentsPaid;

        $totalChildOrgs = Organization::where('college_id', $collegeId)
            ->where('role', 'college_org')
            ->whereNull('mother_organization_id')
            ->count();

        $recentPayments = Payment::with('student')
            ->where('organization_id', $orgId)
            ->latest()
            ->take(10)
            ->get();

        // Simple daily trend
        $dailyPaymentLabels = [];
        $dailyPaymentData = [];

        $daily = Payment::where('organization_id', $orgId)
            ->selectRaw('DATE(created_at) as date, SUM(amount_due) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($daily as $d) {
            $dailyPaymentLabels[] = $d->date;
            $dailyPaymentData[] = $d->total;
        }

        return view('college_org.dashboard', compact(
            'org',
            'totalPaymentsCollected',
            'totalStudentsPaid',
            'pendingStudents',
            'totalChildOrgs',
            'recentPayments',
            'dailyPaymentLabels',
            'dailyPaymentData'
        ));
    }
}