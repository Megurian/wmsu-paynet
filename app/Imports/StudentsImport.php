<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $collegeId = auth()->user()->college_id;

        $courses = Course::where('college_id', $collegeId)->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);
        $years = YearLevel::where('college_id', $collegeId)->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);
        $sections = Section::where('college_id', $collegeId)->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);

        foreach ($rows as $row) {

            $courseId = $courses[strtoupper($row['course'])] ?? null;
            $yearId = $years[strtoupper($row['year_level'])] ?? null;
            $sectionId = $sections[strtoupper($row['section'])] ?? null;

            Student::updateOrCreate(
                ['student_id' => $row['student_id']],
                [
                    'last_name'      => $row['last_name'] ?? null,
                    'first_name'     => $row['first_name'] ?? null,
                    'middle_name'    => $row['middle_name'] ?? null,
                    'suffix'         => $row['suffix'] ?? null,
                    'course_id'      => $courseId,
                    'year_level_id'  => $yearId,
                    'section_id'     => $sectionId,
                    'contact'        => $row['contact'] ?? null,
                    'email'          => $row['email'] ?? null,
                    'college_id'     => $collegeId,
                ]
            );
        }
    }
}
