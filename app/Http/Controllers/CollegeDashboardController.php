<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentEnrollment;
use App\Models\Fee;
use App\Models\Organization;
use App\Models\Payment;

class CollegeDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $collegeId = $user->college_id;

        $activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
        $activeSem = \App\Models\Semester::where('is_active', true)->first();
        $organizations = Organization::where('college_id', $collegeId)->get();

        if (! $activeSY || ! $activeSem) {
            $totalStudents = 0;
        $totalFees = 0;
        $totalPayments = 0;
        $pendingApprovals = 0;
        $recentStudents = collect();
        $recentFees = collect();

        return view('college.dashboard', compact(
            'totalStudents',
            'totalFees',
            'totalPayments',
            'pendingApprovals',
            'recentStudents',
            'recentFees',
            'organizations'
        ))->with('message', 'No active school year or semester is currently set.');
    }

    $totalStudents = StudentEnrollment::where('college_id', $collegeId)
        ->where('school_year_id', $activeSY->id)
        ->where('semester_id', $activeSem->id)
        ->where('status', 'ENROLLED')
        ->count();

    $totalFees = Fee::where('college_id', $collegeId)->count();

    $totalPayments = Payment::whereHas('student', function ($q) use ($collegeId) {
        $q->whereHas('enrollments', fn($q2) => $q2
            ->where('college_id', $collegeId)
            ->where('status', 'ENROLLED')
        );
    })->count();

    $pendingApprovals = Fee::where('college_id', $collegeId)
        ->where('status', 'pending')
        ->count();

    $recentStudents = StudentEnrollment::with('student')
        ->where('college_id', $collegeId)
        ->where('school_year_id', $activeSY->id)
        ->where('semester_id', $activeSem->id)
        ->where('status', 'ENROLLED')
        ->latest('id')
        ->take(5)
        ->get();

    $recentFees = Fee::where('college_id', $collegeId)
        ->latest()
        ->take(5)
        ->get();

    $organizations = Organization::where('college_id', $collegeId)->get();

    return view('college.dashboard', compact(
        'totalStudents',
        'totalFees',
        'totalPayments',
        'pendingApprovals',
        'recentStudents',
        'recentFees',
        'organizations'
    ));
}
}
