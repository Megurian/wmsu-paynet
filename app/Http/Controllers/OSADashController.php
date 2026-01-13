<?php

namespace App\Http\Controllers;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class OSADashController extends Controller
{
    public function index()
    {
        $latestSchoolYear = SchoolYear::with('semesters')
            ->where('is_active', 1) // only the active school year
            ->latest('sy_start')
            ->first();

        return view('osa.dashboard', compact('latestSchoolYear'));
    }
}
