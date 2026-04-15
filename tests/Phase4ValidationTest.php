<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Phase4ValidationTest extends TestCase
{
    public function test_student_portal_controller_exposes_promissory_note_lifecycle_methods()
    {
        $controller = new \App\Http\Controllers\Student\StudentPortalController();

        foreach (['showPromissoryNotes', 'downloadPromissoryNoteTemplate', 'uploadSignedPromissoryNote'] as $method) {
            $this->assertTrue(method_exists($controller, $method), "StudentPortalController missing method: {$method}");
        }
    }

    public function test_student_portal_signature_upload_uses_header_validation_and_unique_filenames()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/Student/StudentPortalController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('str_starts_with($header,', $contents);
        $this->assertStringContainsString('uniqid()', $contents);
        $this->assertStringContainsString('Awaiting coordinator review.', $contents);
    }

    public function test_coordinator_controller_handles_mandatory_signature_review_actions()
    {
        $controller = new \App\Http\Controllers\CoordinatorController();

        foreach (['approveSignature', 'rejectSignature', 'viewDocument'] as $method) {
            $this->assertTrue(method_exists($controller, $method), "CoordinatorController missing method: {$method}");
        }

        $path = dirname(__DIR__) . '/app/Http/Controllers/CoordinatorController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('review_confirmed', $contents);
        $this->assertStringContainsString('lockForUpdate()', $contents);
        $this->assertStringContainsString('PromissoryNoteSignatureApprovedNotification', $contents);
    }

    public function test_validate_students_view_opens_promissory_preview_modal_before_creation()
    {
        $path = dirname(__DIR__) . '/resources/views/college/validate_students.blade.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('showPromissoryPreviewModal', $contents);
        $this->assertStringContainsString('openPromissoryPreview()', $contents);
        $this->assertStringContainsString('Finalize Promissory Note', $contents);
        $this->assertStringContainsString('selected_fee_ids[]', $contents);
    }

    public function test_dashboard_sidebar_removes_pn_reports_link()
    {
        $path = dirname(__DIR__) . '/resources/views/layouts/dashboard.blade.php';
        $contents = file_get_contents($path);

        $this->assertStringNotContainsString('PN Reports', $contents);
        $this->assertStringNotContainsString('college.promissory_notes.dashboard', $contents);
    }

    public function test_promissory_note_void_detaches_linked_fees()
    {
        $path = dirname(__DIR__) . '/app/Models/PromissoryNote.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('fees()->detach()', $contents);
        $this->assertStringContainsString('STATUS_VOIDED', $contents);
    }

    public function test_signature_deadline_command_is_scheduled_daily()
    {
        $path = dirname(__DIR__) . '/routes/console.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString("promissory-notes:check-signature-deadline", $contents);
        $this->assertStringContainsString("->daily();", $contents);
    }

    public function test_student_routes_expose_promissory_note_actions()
    {
        $path = dirname(__DIR__) . '/routes/student.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString("/promissory-notes", $contents);
        $this->assertStringContainsString("promissory_notes.download", $contents);
        $this->assertStringContainsString("promissory_notes.sign", $contents);
    }
}