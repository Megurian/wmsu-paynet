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
    $isAdviser = $user->role === 'adviser';
    $adviserId = $isAdviser ? $user->id : null;

    $activeSY = \App\Models\SchoolYear::where('is_active', true)->first();
    $activeSem = \App\Models\Semester::where('is_active', true)->first();

    $organizations = Organization::where('college_id', $collegeId)->get();

    if (! $activeSY || ! $activeSem) {
        return view('college.dashboard', [
            'totalStudents' => 0,
            'totalFees' => 0,
            'totalPayments' => 0,
            'pendingApprovals' => 0,
            'recentStudents' => collect(),
            'recentFees' => collect(),
            'organizations' => $organizations,
        ])->with('message', 'No active school year or semester is currently set.');
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENTS (FILTERED FOR ADVISER)
    |--------------------------------------------------------------------------
    */
    $studentQuery = StudentEnrollment::query()
        ->where('school_year_id', $activeSY->id)
        ->where('semester_id', $activeSem->id)
        ->where('status', 'ENROLLED');

    if ($isAdviser) {
        $studentQuery->where('adviser_id', $adviserId);
    } else {
        $studentQuery->where('college_id', $collegeId);
    }

    $totalStudents = $studentQuery->count();

    /*
    |--------------------------------------------------------------------------
    | FEES (college-wide OR optionally adviser scope if needed)
    |--------------------------------------------------------------------------
    */
    $totalFees = Fee::where('college_id', $collegeId)->count();

    /*
    |--------------------------------------------------------------------------
    | PAYMENTS (IMPORTANT FIX)
    |--------------------------------------------------------------------------
    */
    $totalPayments = Payment::whereHas('student.enrollments', function ($q) use ($collegeId, $isAdviser, $adviserId, $activeSY, $activeSem) {
        $q->where('school_year_id', $activeSY->id)
          ->where('semester_id', $activeSem->id)
          ->where('status', 'ENROLLED');

        if ($isAdviser) {
            $q->where('adviser_id', $adviserId);
        } else {
            $q->where('college_id', $collegeId);
        }
    })->count();

    /*
    |--------------------------------------------------------------------------
    | PENDING APPROVALS
    |--------------------------------------------------------------------------
    */
    $pendingApprovalsQuery = Fee::where('college_id', $collegeId)
        ->where('status', 'pending');

    $pendingApprovals = $pendingApprovalsQuery->count();

    /*
    |--------------------------------------------------------------------------
    | RECENT STUDENTS
    |--------------------------------------------------------------------------
    */
    $recentStudentsQuery = StudentEnrollment::with('student')
        ->where('school_year_id', $activeSY->id)
        ->where('semester_id', $activeSem->id)
        ->where('status', 'ENROLLED');

    if ($isAdviser) {
        $recentStudentsQuery->where('adviser_id', $adviserId);
    } else {
        $recentStudentsQuery->where('college_id', $collegeId);
    }

    $recentStudents = $recentStudentsQuery
        ->latest('id')
        ->take(5)
        ->get();

    /*
    |--------------------------------------------------------------------------
    | RECENT FEES (unchanged unless you want adviser-specific fees)
    |--------------------------------------------------------------------------
    */
    $recentFees = Fee::where('college_id', $collegeId)
        ->latest()
        ->take(5)
        ->get();

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
