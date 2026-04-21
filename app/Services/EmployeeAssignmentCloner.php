<?php

namespace App\Services;

use App\Models\EmployeeAssignment;
use App\Models\SchoolYear;
use App\Models\Semester;

class EmployeeAssignmentCloner
{
    public function cloneFromPrevious(SchoolYear $newSY, ?Semester $newSem = null): void
    {
        $previousSY = SchoolYear::where('id', '!=', $newSY->id)
            ->orderByDesc('sy_start')
            ->first();

        if (!$previousSY) return;

        $previousSem = $previousSY->semesters()
            ->orderByDesc('starts_at')
            ->first();

        if (!$previousSem) return;

        $previousAssignments = EmployeeAssignment::where('school_year_id', $previousSY->id)
            ->where('semester_id', $previousSem->id)
            ->get();

        foreach ($previousAssignments as $assignment) {

            EmployeeAssignment::firstOrCreate(
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