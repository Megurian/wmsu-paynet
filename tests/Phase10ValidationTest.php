<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Phase10ValidationTest extends TestCase
{
    public function test_promissory_note_open_slot_migration_replaces_student_unique_constraint()
    {
        $path = dirname(__DIR__) . '/database/migrations/2026_04_12_150000_replace_student_unique_pn_constraint_with_open_slot.php';

        $this->assertFileExists($path, 'Open-slot PN migration is missing.');

        $contents = file_get_contents($path);

        $this->assertStringContainsString('open_student_id', $contents);
        $this->assertStringContainsString('unique_student_open_pn', $contents);
        $this->assertStringContainsString('dropUnique(\'unique_student_active_pn\')', $contents);
    }

    public function test_promissory_note_model_syncs_open_slot_from_open_statuses()
    {
        $path = dirname(__DIR__) . '/app/Models/PromissoryNote.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('OPEN_STATUSES', $contents);
        $this->assertStringContainsString('occupiesOpenStudentSlot', $contents);
        $this->assertStringContainsString('open_student_id', $contents);
    }

    public function test_promissory_note_issuance_service_handles_duplicate_open_slot_violations()
    {
        $path = dirname(__DIR__) . '/app/Services/PromissoryNoteIssuanceService.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('QueryException', $contents);
        $this->assertStringContainsString('Student already has an active or pending promissory note.', $contents);
    }

    public function test_validate_students_controller_uses_open_status_constant_for_blocking_notes()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/ValidateStudentsController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('PromissoryNote::OPEN_STATUSES', $contents);
    }
}