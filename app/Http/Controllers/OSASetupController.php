<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolYear;
use App\Models\Semester;

class OSASetupController extends Controller
{
    public function edit()
    {
        $schoolYears = SchoolYear::with('semesters')->orderBy('sy_start', 'desc')->get();
        $latestSchoolYear = SchoolYear::latest('created_at')->first();

        $existingSemesters = [];
        if ($latestSchoolYear) {
            $existingSemesters = $latestSchoolYear->semesters->pluck('name')->toArray();
        }

        return view('osa.setup', compact('schoolYears', 'latestSchoolYear', 'existingSemesters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sy_start' => 'required|date',
            'sy_end'   => 'required|date|after_or_equal:sy_start',
        ]);

        SchoolYear::create([
            'sy_start' => $request->sy_start,
            'sy_end'   => $request->sy_end,
            'is_active' => false,
        ]);

        return redirect()->back()->with('status', 'School Year added successfully!');
    }

    public function addSemester(Request $request, $schoolYearId)
    {
        $request->validate([
            'semester' => 'required|in:1st,2nd,summer',
        ]);

        $schoolYear = SchoolYear::findOrFail($schoolYearId);

        $schoolYear->semesters()->where('is_active', true)->update(['is_active' => false]);

        
        Semester::create([
            'school_year_id' => $schoolYear->id,
            'name' => $request->semester,
            'is_active' => true,
        ]);

        return redirect()->back()->with('status', 'Semester added successfully!');
    }
}
