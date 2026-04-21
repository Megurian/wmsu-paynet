<?php

namespace App\Observers;

use App\Models\Semester;
use App\Services\EmployeeAssignmentCloner;

class SemesterObserver
{
    public function updated(Semester $semester): void
    {
        // trigger only when it becomes active
        if (!$semester->is_active) return;

        app(EmployeeAssignmentCloner::class)
            ->cloneFromPrevious($semester->schoolYear, $semester);
    }
}