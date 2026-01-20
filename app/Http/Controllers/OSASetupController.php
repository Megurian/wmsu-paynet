<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // Ensure the school year spans across different calendar years
        $startYear = Carbon::parse($request->sy_start)->year;
        $endYear = Carbon::parse($request->sy_end)->year;

        if ($startYear === $endYear) {
            return back()->withErrors([
                'sy_end' => 'Start and end dates must span different calendar years (e.g., 2024–2025).',
            ])->withInput();
        }

        // Prevent creating a new school year until the current active school year has
        // completed 1st -> 2nd -> summer (hard-coded requirement)
        $activeSY = SchoolYear::where('is_active', true)->with('semesters')->first();
        if ($activeSY) {
            $semNames = $activeSY->semesters->pluck('name')->toArray();
            $required = ['1st', '2nd', 'summer'];
            $hasAll = count(array_intersect($required, $semNames)) === count($required);
            $hasAnyActive = $activeSY->semesters->contains('is_active', true);

            if (!$hasAll || $hasAnyActive) {
                return back()->withErrors([
                    'sy_start' => 'Cannot create a new school year until the active school year has completed 1st, 2nd, and summer semesters.'
                ])->withInput();
            }
        }

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

        try {
            $newSY = null;
            DB::transaction(function () use ($request, &$newSY) {
                // Deactivate all previous school years and their semesters
                SchoolYear::where('is_active', true)->update(['is_active' => false]);
                Semester::where('is_active', true)->update(['is_active' => false]);

                // Create new school year as active
                $newSY = SchoolYear::create([
                    'sy_start' => $request->sy_start,
                    'sy_end'   => $request->sy_end,
                    'is_active' => true,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create School Year: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['db' => 'Failed to create new school year. Please try again.'])->withInput();
        }

        return redirect()->back()->with('status', 'New School Year added and activated successfully!');
    }

    public function addSemester(Request $request, $schoolYearId)
    {
        $request->validate([
            'semester' => 'required|in:1st,2nd,summer',
        ]);

        $schoolYear = SchoolYear::findOrFail($schoolYearId);

        try {
            DB::transaction(function () use ($schoolYear, $request) {
                $schoolYear->semesters()->update(['is_active' => false]);
                Semester::create([
                    'school_year_id' => $schoolYear->id,
                    'name' => $request->semester,
                    'is_active' => true,
                ]);
                $schoolYear->update(['is_active' => true]);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to add semester: ' . $e->getMessage(), ['exception' => $e, 'school_year_id' => $schoolYearId]);
            return back()->withErrors(['db' => 'Failed to add semester. Please try again.'])->withInput();
        }

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

        try {
            DB::transaction(function () use ($activeSemester) {
                $activeSemester->update([
                    'is_active' => false,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to end semester: ' . $e->getMessage(), ['exception' => $e, 'school_year_id' => $schoolYearId]);
            return back()->withErrors(['db' => 'Failed to end semester. Please try again.']);
        }

        return back()->with('status', 'Semester ended successfully.');
    }

}
