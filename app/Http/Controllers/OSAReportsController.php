<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\College;
use App\Models\Organization;

class OSAReportsController extends Controller
{
    public function index()
    {
        $colleges = College::withCount('users')->get();

        $collegeOrgCounts = Organization::selectRaw(
            'college_id, 
             SUM(CASE WHEN mother_organization_id IS NULL THEN 1 ELSE 0 END) as local_orgs_count,
             SUM(CASE WHEN mother_organization_id IS NOT NULL THEN 1 ELSE 0 END) as child_orgs_count'
        )
        ->groupBy('college_id')
        ->pluck('local_orgs_count', 'college_id')
        ->toArray();

        $collegeChildCounts = Organization::selectRaw(
            'college_id, 
             SUM(CASE WHEN mother_organization_id IS NOT NULL THEN 1 ELSE 0 END) as child_orgs_count'
        )
        ->groupBy('college_id')
        ->pluck('child_orgs_count', 'college_id')
        ->toArray();

        foreach ($colleges as $college) {
            $college->local_orgs_count = $collegeOrgCounts[$college->id] ?? 0;
            $college->child_orgs_count = $collegeChildCounts[$college->id] ?? 0;
        }

        return view('osa.reports', compact('colleges'));
    }

    public function generate(Request $request)
    {
     
    }
}