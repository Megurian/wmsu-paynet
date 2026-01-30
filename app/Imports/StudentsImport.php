<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $adviserId = auth()->id();
        $collegeId = auth()->user()->college_id;

        $activeSY = SchoolYear::where('is_active', true)->first();
        $activeSem = Semester::where('is_active', true)->first();

        $courses = Course::where('college_id', $collegeId)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);

        $years = YearLevel::where('college_id', $collegeId)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);

        $sections = Section::where('college_id', $collegeId)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);

        foreach ($rows as $row) {
            $studentId = $row['student_id'] ?? null;
            if (!$studentId) {
                Log::warning('Skipping row with missing student_id: ' . json_encode($row));
                continue;
            }

            // Create or update student
            $student = Student::updateOrCreate(
                ['student_id' => $studentId],
                [
                    'last_name'     => $row['last_name'] ?? null,
                    'first_name'    => $row['first_name'] ?? null,
                    'middle_name'   => $row['middle_name'] ?? null,
                    'suffix'        => $row['suffix'] ?? null,
                    'college_id'    => $collegeId,
                    'contact'       => $row['contact'] ?? null,
                    'email'         => $row['email'] ?? null,
                ]
            );

            $courseId = $courses[strtoupper($row['course'] ?? '')] ?? null;
            $yearId = $years[strtoupper($row['year_level'] ?? '')] ?? null;
            $sectionId = $sections[strtoupper($row['section'] ?? '')] ?? null;

            if (!$courseId || !$yearId || !$sectionId) {
                $prev = StudentEnrollment::where('student_id', $student->id)
                    ->where(function ($q) use ($activeSY, $activeSem) {
                        $q->where('school_year_id', '<', $activeSY->id)
                          ->orWhere(function ($q2) use ($activeSY, $activeSem) {
                              $q2->where('school_year_id', $activeSY->id)
                                 ->where('semester_id', '<', $activeSem->id);
                          });
                    })
                    ->latest('id')
                    ->first();

                $courseId ??= $prev?->course_id;
                $yearId ??= $prev?->year_level_id;
                $sectionId ??= $prev?->section_id;
            }

            if ($activeSY && $activeSem && $courseId && $yearId && $sectionId) {
                StudentEnrollment::updateOrCreate(
                    [
                        'student_id'     => $student->id,
                        'school_year_id' => $activeSY->id,
                        'semester_id'    => $activeSem->id,
                    ],
                    [
                        'college_id'    => $collegeId,
                        'adviser_id'    => $adviserId,
                        'course_id'     => $courseId,
                        'year_level_id' => $yearId,
                        'section_id'    => $sectionId,
                        'status'        => 'FOR_PAYMENT_VALIDATION',
                    ]
                );
            } else {
                Log::warning("Skipping enrollment for student {$student->student_id} due to missing course/year/section");
            }
        }
    }
}
