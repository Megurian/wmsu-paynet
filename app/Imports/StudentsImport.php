<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\YearLevel;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StudentsImport implements ToCollection, WithHeadingRow
{
    protected int $created  = 0;
    protected int $updated  = 0;
    protected int $skipped  = 0;
    protected array $skippedRows = [];

    public function collection(Collection $rows)
    {
        $adviser        = auth()->user();
        $adviserId      = $adviser->id;
        $collegeId      = $adviser->college_id;
        $adviserCourseId = $adviser->course_id;

        $activeSY  = SchoolYear::where('is_active', true)->first() ?? SchoolYear::latest()->first();
        $activeSem = Semester::where('is_active', true)->first() ?? Semester::latest()->first();

        $years = YearLevel::where('college_id', $collegeId)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);

        $sections = Section::where('college_id', $collegeId)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);

        foreach ($rows as $row) {
            $row = collect($row)->mapWithKeys(function ($value, $key) {
                return [strtolower(str_replace(' ', '_', $key)) => $value];
            });

            $studentId = trim($row['student_id'] ?? '');
            $lastName  = trim($row['last_name']  ?? '');
            $firstName = trim($row['first_name'] ?? '');

            if (!$studentId || !$lastName || !$firstName) {
                Log::warning('Skipping invalid row (missing required fields): ' . json_encode($row));
                continue;
            }

            // Cross-reference: if student_id already exists, last_name MUST match
            $existing = Student::where('student_id', $studentId)->first();

            if ($existing && strtolower($existing->last_name) !== strtolower($lastName)) {
                // student_id found but last_name doesn't match – skip to avoid overwriting wrong record
                $this->skipped++;
                $this->skippedRows[] = [
                    'student_id'     => $studentId,
                    'file_last_name' => $lastName,
                    'db_last_name'   => $existing->last_name,
                ];
                Log::warning("Skipping student_id {$studentId}: last_name mismatch (file: '{$lastName}', db: '{$existing->last_name}')");
                continue;
            }

            $wasNew = $existing === null;

            $student = Student::updateOrCreate(
                ['student_id' => $studentId],
                [
                    'last_name'   => $lastName,
                    'first_name'  => $firstName,
                    'middle_name' => $row['middle_name'] ?? null,
                    'suffix'      => $row['suffix']      ?? null,
                    'contact'     => $row['contact']     ?? null,
                    'email'       => $row['email']       ?? null,
                    'religion'    => $row['religion']    ?? null,
                ]
            );

            $wasNew ? $this->created++ : $this->updated++;

            $courseId  = $adviserCourseId;

            $yearValue = trim((string) ($row['year_level'] ?? ''));
            $sectionValue = trim((string) ($row['section'] ?? ''));

            $yearId = $years[strtoupper($yearValue)] ?? null;
            $sectionId = $sections[strtoupper($sectionValue)] ?? null;

            // Fallback: allow numeric IDs or match by exact name (case-insensitive)
            if (!$yearId && is_numeric($yearValue)) {
                // The template may use plain numbers (e.g. "1"), while the database stores names like "1st Year"
                // Prefer matching on name prefixes like "1" -> "1st Year" when available.
                $yearId = YearLevel::where('college_id', $collegeId)
                    ->where(function ($q) use ($yearValue) {
                        $q->where('id', (int)$yearValue)
                          ->orWhereRaw('LOWER(name) LIKE ?', [strtolower($yearValue) . '%']);
                    })
                    ->value('id');
            }

            if (!$yearId && $yearValue !== '') {
                $yearId = YearLevel::where('college_id', $collegeId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($yearValue)])
                    ->value('id');
            }

            if (!$sectionId && is_numeric($sectionValue)) {
                $sectionId = Section::find((int)$sectionValue)?->id;
            }

            if (!$sectionId && $sectionValue !== '') {
                $sectionId = Section::where('college_id', $collegeId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($sectionValue)])
                    ->value('id');
            }

            if (!$yearId || !$sectionId) {
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

                $yearId    ??= $prev?->year_level_id;
                $sectionId ??= $prev?->section_id;
            }

            if ($activeSY && $activeSem && $courseId && $yearId && $sectionId) {
                // Use firstOrNew so we never downgrade a student's status that has already advanced
                $enrollment = StudentEnrollment::firstOrNew([
                    'student_id'     => $student->id,
                    'school_year_id' => $activeSY->id,
                    'semester_id'    => $activeSem->id,
                ]);

                $enrollment->fill([
                    'college_id'    => $collegeId,
                    'adviser_id'    => $adviserId,
                    'course_id'     => $courseId,
                    'year_level_id' => $yearId,
                    'section_id'    => $sectionId,
                ]);

                if (!$enrollment->exists) {
                    $enrollment->status = 'NOT_ENROLLED';
                }

                $enrollment->save();
            } else {
                Log::warning("Skipping enrollment for student {$student->student_id} due to missing course/year/section");
            }
        }
    }

    public function getResult(): array
    {
        return [
            'created'      => $this->created,
            'updated'      => $this->updated,
            'skipped'      => $this->skipped,
            'skipped_rows' => $this->skippedRows,
        ];
    }
}