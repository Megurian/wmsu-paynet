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
        $latestSchoolYear = SchoolYear::where('is_active', true)->with('semesters')->first();

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

        // Deactivate all previous school years and their semesters
        SchoolYear::where('is_active', true)->update(['is_active' => false]);
        Semester::where('is_active', true)->update(['is_active' => false]);

        // Create new school year as active
        $newSY = SchoolYear::create([
            'sy_start' => $request->sy_start,
            'sy_end'   => $request->sy_end,
            'is_active' => true,
        ]);

        return redirect()->back()->with('status', 'New School Year added and activated successfully!');
    }

    public function addSemester(Request $request, $schoolYearId)
    {
        $request->validate([
            'semester' => 'required|in:1st,2nd,summer',
        ]);

        $schoolYear = SchoolYear::findOrFail($schoolYearId);

        // Deactivate all previous semesters for this S.Y
        $schoolYear->semesters()->update(['is_active' => false]);

        // Create new active semester
        Semester::create([
            'school_year_id' => $schoolYear->id,
            'name' => $request->semester,
            'is_active' => true,
        ]);

        // Make sure the school year itself is active (if adding a semester activates the S.Y)
        $schoolYear->update(['is_active' => true]);

        return redirect()->back()->with('status', 'Semester added and activated successfully!');
    }
}
