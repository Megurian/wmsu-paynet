<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\College;
use App\Models\Organization;
use App\Models\SchoolYear;
use App\Models\Semester;

class OSAReportsController extends Controller
{
    public function index(Request $request)
    {
        // Get current active S.Y. and semester as default
        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = $activeSY ? $activeSY->semesters()->where('is_active', true)->first() : null;

        $selectedSYId = $request->input('school_year_id', $activeSY->id ?? null);
        $selectedSemId = $request->input('semester_id', $activeSem->id ?? null);

        $colleges = College::withCount('users')->get();

        $orgQuery = Organization::query()
            ->where(function ($q) use ($selectedSYId, $selectedSemId) {
              
                $q->where('created_school_year_id', '<', $selectedSYId)
                    ->orWhere(function ($q2) use ($selectedSYId, $selectedSemId) {
                   
                        $q2->where('created_school_year_id', $selectedSYId)
                            ->where('created_semester_id', '<=', $selectedSemId);
                    });
            })
            ->selectRaw(
                'college_id, 
         SUM(CASE WHEN mother_organization_id IS NULL THEN 1 ELSE 0 END) as local_orgs_count,
         SUM(CASE WHEN mother_organization_id IS NOT NULL THEN 1 ELSE 0 END) as child_orgs_count'
            )
            ->groupBy('college_id')
            ->get()
            ->keyBy('college_id');

        foreach ($colleges as $college) {
            $college->local_orgs_count = $orgQuery[$college->id]->local_orgs_count ?? 0;
            $college->child_orgs_count = $orgQuery[$college->id]->child_orgs_count ?? 0;
        }

        $schoolYears = SchoolYear::orderBy('sy_start', 'desc')->get();
        $semesters = $selectedSYId ? Semester::where('school_year_id', $selectedSYId)->get() : collect();

        return view('osa.reports', compact(
            'colleges',
            'schoolYears',
            'semesters',
            'selectedSYId',
            'selectedSemId',
            'activeSY',
            'activeSem'
        ));
    }
}
