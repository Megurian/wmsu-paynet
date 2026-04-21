<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Semester;

abstract class Controller
{
    protected function getActiveSchoolYear(): ?SchoolYear
    {
        return SchoolYear::where('is_active', true)->first();
    }

    protected function getActiveSemester(): ?Semester
    {
        return Semester::where('is_active', true)->first();
    }

    protected function getActiveAcademicPeriod(): array
    {
        return [
            $this->getActiveSchoolYear(),
            $this->getActiveSemester(),
        ];
    }
}
