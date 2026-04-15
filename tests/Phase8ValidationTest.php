<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Phase8ValidationTest extends TestCase
{
    public function test_semester_migration_adds_ended_at_column()
    {
        $path = dirname(__DIR__) . '/database/migrations/2026_04_12_000001_add_ended_at_to_semesters_table.php';

        $this->assertFileExists($path, 'Semester end-date migration is missing.');
        $contents = file_get_contents($path);

        $this->assertStringContainsString("ended_at", $contents);
        $this->assertStringContainsString("index('ended_at')", $contents);
    }

    public function test_semester_model_exposes_effective_end_date_helper()
    {
        $path = dirname(__DIR__) . '/app/Models/Semester.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString("'ended_at' => 'datetime'", $contents);
        $this->assertStringContainsString("'ended_at'", $contents);
        $this->assertStringContainsString('effectiveEndDate', $contents);
    }

    public function test_semester_closure_is_stampable_from_osa_setup()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/OSASetupController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString("update(['is_active' => false, 'ended_at' => now()])", $contents);
    }

    public function test_validate_students_controller_enforces_pn_due_date_ceiling()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/ValidateStudentsController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('resolvePromissoryNoteDueDateCeiling', $contents);
        $this->assertStringContainsString('due_date_max', $contents);
        $this->assertStringContainsString('due_date_min', $contents);
        $this->assertStringContainsString('semesterStart', $contents);
        $this->assertStringContainsString('semesterEnd', $contents);
        $this->assertStringContainsString('semester start', $contents);
        $this->assertStringContainsString('semester end', $contents);
    }

    public function test_validate_students_preview_ui_exposes_due_date_ceiling()
    {
        $path = dirname(__DIR__) . '/resources/views/college/validate_students.blade.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('promissoryPreviewDueDateMax', $contents);
        $this->assertStringContainsString(':max="promissoryPreviewDueDateMax || null"', $contents);
        $this->assertStringContainsString('dueDateValidationError', $contents);
        $this->assertStringContainsString('semesterStartDate', $contents);
        $this->assertStringContainsString('semesterEndDate', $contents);
    }

    public function test_delinquency_service_uses_semester_deadline_helper()
    {
        $path = dirname(__DIR__) . '/app/Services/PromissoryNoteDelinquencyService.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('currentSemesterDeadline', $contents);
        $this->assertStringContainsString('effectiveEndDate', $contents);
        $this->assertStringContainsString('greaterThanOrEqualTo', $contents);
    }

    public function test_osa_setup_view_shows_semester_end_timestamps()
    {
        $path = dirname(__DIR__) . '/resources/views/osa/setup.blade.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('Ended {{ Carbon::parse($semester->ended_at)->format', $contents);
        $this->assertStringContainsString('Deadline:', $contents);
    }
}
