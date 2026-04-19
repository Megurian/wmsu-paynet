<?php

namespace App\Services;

use App\Models\EmployeeAssignment;
use App\Models\SchoolYear;
use App\Models\Semester;

class EmployeeAssignmentCloner
{
    public function cloneFromPrevious(SchoolYear $newSY, ?Semester $newSem = null)
    {
        // Get previous active school year (now inactive after switch)
        $previousSY = SchoolYear::where('id', '!=', $newSY->id)
            ->orderByDesc('sy_start')
            ->first();

        if (!$previousSY) {
            return;
        }

        // Get previous semester (last active one)
        $previousSem = $previousSY->semesters()
            ->orderByDesc('starts_at')
            ->first();

        if (!$previousSem) {
            return;
        }

        // Fetch previous assignments
        $previousAssignments = EmployeeAssignment::where('school_year_id', $previousSY->id)
            ->where('semester_id', $previousSem->id)
            ->get();

        if ($previousAssignments->isEmpty()) {
            return;
        }

        foreach ($previousAssignments as $assignment) {

            EmployeeAssignment::updateOrCreate(
                [
                    'employee_id' => $assignment->employee_id,
                    'school_year_id' => $newSY->id,
                    'semester_id' => $newSem?->id,
                ],
                [
                    'positions' => $assignment->positions,
                    'course_id' => $assignment->course_id,
                ]
            );
        }
    }
}