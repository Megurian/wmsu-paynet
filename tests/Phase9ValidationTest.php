<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Phase9ValidationTest extends TestCase
{
    public function test_semester_planning_migration_adds_start_and_planned_end_dates()
    {
        $path = dirname(__DIR__) . '/database/migrations/2026_04_12_000002_add_planned_dates_to_semesters_table.php';

        $this->assertFileExists($path, 'Planned semester dates migration is missing.');

        $contents = file_get_contents($path);

        $this->assertStringContainsString('starts_at', $contents);
        $this->assertStringContainsString('will_end_at', $contents);
        $this->assertStringContainsString("index('starts_at')", $contents);
        $this->assertStringContainsString("index('will_end_at')", $contents);
    }

    public function test_semester_model_exposes_planned_end_helper()
    {
        $path = dirname(__DIR__) . '/app/Models/Semester.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString("'starts_at' => 'date'", $contents);
        $this->assertStringContainsString("'will_end_at' => 'date'", $contents);
        $this->assertStringContainsString('plannedStartDate', $contents);
        $this->assertStringContainsString('plannedEndDate', $contents);
        $this->assertStringContainsString('effectiveEndDate', $contents);
    }

    public function test_semester_enum_accepts_uppercase_labels()
    {
        $path = dirname(__DIR__) . '/database/migrations/2026_04_12_000003_expand_semesters_name_enum.php';

        $this->assertFileExists($path, 'Semester enum expansion migration is missing.');

        $contents = file_get_contents($path);

        $this->assertStringContainsString("'1st SEMESTER'", $contents);
        $this->assertStringContainsString("'2nd SEMESTER'", $contents);
        $this->assertStringContainsString("'SUMMER'", $contents);
    }

    public function test_osa_setup_controller_generates_semesters_from_presets()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/OSASetupController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('createAcademicSetup()', $contents);
        $this->assertStringContainsString("'semester_preset' => 'required|in:semestral,trimester,quadmester,custom'", $contents);
        $this->assertStringContainsString("'semester_lengths' => 'nullable|array'", $contents);
        $this->assertStringContainsString("'semester_starts_at' => 'required|array'", $contents);
        $this->assertStringContainsString("'semester_will_end_at' => 'required|array'", $contents);
        $this->assertStringContainsString('buildSemesterPlan(', $contents);
        $this->assertStringContainsString('resolveSemesterPlan(', $contents);
        $this->assertStringContainsString('resolveSemesterWeights(', $contents);
        $this->assertStringContainsString('allocateSemesterDays(', $contents);
        $this->assertStringContainsString('1st SEMESTER', $contents);
        $this->assertStringContainsString('SUMMER', $contents);
    }

    public function test_osa_setup_view_exposes_preset_based_academic_year_modal()
    {
        $path = dirname(__DIR__) . '/resources/views/osa/setup.blade.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('New Academic Year', $contents);
        $this->assertStringContainsString("route('osa.setup.create-academic-setup')", $contents);
        $this->assertStringContainsString('1st SEMESTER', $contents);
        $this->assertStringContainsString('SUMMER', $contents);
    }

    public function test_create_academic_setup_view_contains_editable_timeline_form()
    {
        $path = dirname(__DIR__) . '/resources/views/osa/create-academic-setup.blade.php';
        $this->assertFileExists($path, 'Create academic setup page is missing.');

        $contents = file_get_contents($path);

        $this->assertStringContainsString('Create Academic Year', $contents);
        $this->assertStringContainsString('semester_preset', $contents);
        $this->assertStringContainsString('semesterTimelineContainer', $contents);
        $this->assertStringContainsString('semesterLengthsContainer', $contents);
        $this->assertStringContainsString('semester_starts_at[]', $contents);
        $this->assertStringContainsString('semester_will_end_at[]', $contents);
        $this->assertStringContainsString('Editable semester timeline', $contents);
    }
}