<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Lightweight import class used only for reading rows during the preview step.
 * Does NOT write anything to the database.
 */
class StudentsImportPreview implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        // intentionally empty — rows are returned via Excel::toCollection()
    }
}
