<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SchoolYear;

class CheckActiveSchoolYear
{
    public function handle(Request $request, Closure $next)
    {
        $activeSY = SchoolYear::where('is_active', true)->with('semesters')->first();

        if (!$activeSY || $activeSY->semesters->where('is_active', true)->isEmpty()) {
            return redirect()->route('osa.setup')
                ->with('status', 'You must create and activate a School Year and Semester before accessing the system.');
        }

        return $next($request);
    }
}
