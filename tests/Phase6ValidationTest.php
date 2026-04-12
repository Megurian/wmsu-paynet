<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Phase6ValidationTest extends TestCase
{
    public function test_promissory_note_report_export_uses_query_based_streaming()
    {
        $path = dirname(__DIR__) . '/app/Exports/PromissoryNoteReportExport.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('implements FromQuery, WithMapping, WithHeadings', $contents,
            'PromissoryNoteReportExport should use FromQuery to stream large datasets.');
        $this->assertStringNotContainsString('implements FromArray', $contents,
            'PromissoryNoteReportExport should no longer use FromArray for export buffering.');
    }

    public function test_coordinator_export_filename_includes_school_year_and_semester_context()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/CoordinatorController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('promissory-note-report-%s-%s-%s.csv', $contents,
            'Coordinator export filename should include school year and semester context.');
    }

    public function test_coordinator_reporting_context_validates_semester_school_year_association()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/CoordinatorController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString(
            'Semester::where(\'school_year_id\', $selectedSchoolYear->id)',
            $contents,
            'Coordinator reporting context should validate that the selected semester belongs to the selected school year.'
        );

        $this->assertStringContainsString(
            '->findOrFail((int) $request->input(\'semester_id\'))',
            $contents,
            'Coordinator reporting context should validate semester selection using semester_id and school_year_id.'
        );
    }

    public function test_school_year_model_casts_dates()
    {
        $path = dirname(__DIR__) . '/app/Models/SchoolYear.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString("'sy_start' => 'date'", $contents,
            'SchoolYear model should cast sy_start to date.');
        $this->assertStringContainsString("'sy_end' => 'date'", $contents,
            'SchoolYear model should cast sy_end to date.');
    }

    public function test_coordinator_dashboard_authorizes_authenticated_user_before_reporting()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/CoordinatorController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('abort_unless(Auth::user(), 403);', $contents,
            'Coordinator dashboard and export should verify the authenticated user instance before processing.');
    }
}
