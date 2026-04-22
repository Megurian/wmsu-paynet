<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog; // or whatever your logs table is

class CollegeLogController extends Controller
{
    public function index()
    {
        $collegeId = auth()->user()->college_id;

        $logs = ActivityLog::with(['user', 'student'])
            ->where('college_id', $collegeId)
            ->latest()
            ->paginate(20);

        return view('college.logs', compact('logs'));
    }
}