<?php

namespace App\Observers;

use App\Models\SchoolYear;
use App\Services\EmployeeAssignmentCloner;

class SchoolYearObserver
{
    public function created(SchoolYear $schoolYear): void
    {
        // only run if active
        if (!$schoolYear->is_active) return;

        app(EmployeeAssignmentCloner::class)
            ->cloneFromPrevious($schoolYear, $schoolYear->activeSemester);
    }
}