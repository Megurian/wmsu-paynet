<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StudentTemplateGenerator
{
    /**
     * Create a student CSV template at storage/app/private/templates/student_template.csv
     * only if it does not already exist.
     *
     * @return bool  True when file was created; false when it already existed.
     */
    public static function generateIfNotExists(): bool
    {
        $path = 'templates/student_template.csv';
        $oldPath = 'private/templates/student_template.csv';
        
        // Migrate from incorrect location if needed
        if (Storage::disk('local')->exists($oldPath) && ! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->move($oldPath, $path);
        }

        if (Storage::disk('local')->exists($path)) {
            return false;
        }

        $headers = [
            'Student ID',
            'Last Name',
            'First Name',
            'Middle Name',
            'Suffix',
            'Year level',
            'Section',
            'Contact',
            'Email',
            'Religion',
        ];

        // headers contain no commas so a simple join is fine for the CSV header row
        $content = implode(',', $headers) . PHP_EOL;

        Storage::disk('local')->put($path, $content);

        return true;
    }
}
