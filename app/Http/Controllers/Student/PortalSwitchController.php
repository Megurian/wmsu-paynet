<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PortalSwitchController extends Controller
{
    public function toOrganization()
{
    $student = Auth::guard('student')->user();

    if (! $student->hasOrganizationAccess()) {
        abort(403);
    }

    session(['portal_mode' => 'organization']);

    Auth::loginUsingId($student->id);

    return redirect()->route('college_org.dashboard');
}

    public function toStudent()
    {
        session(['portal_mode' => 'student']);

        return redirect('/student/dashboard');
    }
}