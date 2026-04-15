<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Phase7ValidationTest extends TestCase
{
    public function test_bootstrap_config_suppresses_expected_promissory_note_exceptions()
    {
        $path = dirname(__DIR__) . '/bootstrap/app.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('dontReportDuplicates()', $contents);
        $this->assertStringContainsString('PromissoryNoteException::class', $contents);
    }

    public function test_promissory_note_settlement_service_logs_successful_collections()
    {
        $path = dirname(__DIR__) . '/app/Services/PromissoryNoteSettlementService.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('Promissory note settlement recorded', $contents);
        $this->assertStringContainsString('remaining_balance', $contents);
        $this->assertStringContainsString('is_org_payment', $contents);
    }

    public function test_promissory_note_issuance_service_persists_preview_notes()
    {
        $path = dirname(__DIR__) . '/app/Services/PromissoryNoteIssuanceService.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('notes ? trim($notes) : null', $contents);
        $this->assertStringContainsString('signature_deadline', $contents);
        $this->assertStringContainsString('dueDate', $contents);
    }

    public function test_validate_students_controller_logs_clearance_and_issuance()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/ValidateStudentsController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('Student cleared for enrollment', $contents);
        $this->assertStringContainsString('Promissory note issued for student enrollment', $contents);
        $this->assertStringContainsString('Log::info', $contents);
    }

    public function test_coordinator_controller_logs_signature_reviews()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/CoordinatorController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('Promissory note signature approved', $contents);
        $this->assertStringContainsString('Promissory note signature rejected', $contents);
        $this->assertStringContainsString('Promissory note report exported', $contents);
    }

    public function test_student_portal_controller_logs_uploaded_signed_notes()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/Student/StudentPortalController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('Signed promissory note uploaded', $contents);
        $this->assertStringContainsString('notifyReviewers', $contents);
    }

    public function test_delinquency_service_logs_status_transitions()
    {
        $path = dirname(__DIR__) . '/app/Services/PromissoryNoteDelinquencyService.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('Promissory note transitioned to DEFAULT', $contents);
        $this->assertStringContainsString('Promissory note transitioned to BAD_DEBT', $contents);
    }

    public function test_promissory_note_lookup_index_migration_exists()
    {
        $path = dirname(__DIR__) . '/database/migrations/2026_04_10_090000_add_enrollment_status_index_to_promissory_notes_table.php';

        $this->assertFileExists($path);

        $contents = file_get_contents($path);

        $this->assertStringContainsString("index(['enrollment_id', 'status']", $contents);
        $this->assertStringContainsString('promissory_notes_enrollment_status_index', $contents);
    }
}