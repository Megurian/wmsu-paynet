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

        $exists = SchoolYear::where(function ($q) use ($request) {
            $q->whereBetween('sy_start', [$request->sy_start, $request->sy_end])
            ->orWhereBetween('sy_end', [$request->sy_start, $request->sy_end])
            ->orWhere(function ($q2) use ($request) {
                $q2->where('sy_start', '<=', $request->sy_start)
                    ->where('sy_end', '>=', $request->sy_end);
            });
        })->exists();

        if ($exists) {
            return back()->withErrors([
                'sy_start' => 'The school year overlaps with an existing school year.',
            ])->withInput();
        }
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
        $schoolYear->semesters()->update(['is_active' => false]);
        Semester::create([
            'school_year_id' => $schoolYear->id,
            'name' => $request->semester,
            'is_active' => true,
        ]);
        $schoolYear->update(['is_active' => true]);

        return redirect()->back()->with('status', 'Semester added and activated successfully!');
    }

    public function endSemester($schoolYearId)
    {
        $schoolYear = SchoolYear::with('semesters')->findOrFail($schoolYearId);
        $activeSemester = $schoolYear->semesters()
            ->where('is_active', true)
            ->first();

        if (!$activeSemester) {
            return back()->withErrors('No active semester to end.');
        }
        $activeSemester->update([
            'is_active' => false,
        ]);
        return back()->with('status', 'Semester ended successfully.');
    }

}
