<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Phase3ValidationTest extends TestCase
{
    public function test_phase3_migration_defines_financial_status_column()
    {
        $path = dirname(__DIR__) . '/database/migrations/2026_04_10_000001_add_unpaid_financial_status_to_student_enrollments.php';

        $this->assertFileExists($path, 'Phase 3 migration file is missing.');
        $contents = file_get_contents($path);

        $this->assertStringContainsString('financial_status', $contents);
        $this->assertStringContainsString('ALTER TABLE student_enrollments MODIFY financial_status', $contents);
        $this->assertStringContainsString("'UNPAID'", $contents);
        $this->assertStringContainsString("'PAID'", $contents);
        $this->assertStringContainsString("'PARTIALLY_PAID'", $contents);
        $this->assertStringContainsString("'DEFERRED'", $contents);
        $this->assertStringContainsString("'DEFAULT'", $contents);
        $this->assertStringContainsString("'BAD_DEBT'", $contents);
    }

    public function test_adviser_upload_paths_default_financial_status_to_unpaid()
    {
        foreach ([
            dirname(__DIR__) . '/app/Http/Controllers/AdviserStudentUploadController.php',
            dirname(__DIR__) . '/app/Imports/StudentsImport.php',
        ] as $path) {
            $contents = file_get_contents($path);

            $this->assertStringContainsString('StudentEnrollment::FINANCIAL_UNPAID', $contents);
        }
    }

    public function test_student_enrollment_has_phase3_contract()
    {
        $model = new \App\Models\StudentEnrollment();

        $this->assertSame('UNPAID', \App\Models\StudentEnrollment::FINANCIAL_UNPAID);

        foreach (['refreshFinancialStatus', 'hasActivePromissoryNote', 'hasUnpaidPriorNote', 'computeFinancialStatus'] as $method) {
            $this->assertTrue(method_exists($model, $method), "StudentEnrollment missing method: {$method}");
        }

        foreach (['scopeFinanciallyCleared', 'scopeFinanciallyDeferred'] as $method) {
            $this->assertTrue(method_exists($model, $method), "StudentEnrollment missing scope: {$method}");
        }

        $this->assertContains('financial_status', $model->getFillable());
    }

    public function test_promissory_note_observer_exists()
    {
        $this->assertTrue(
            class_exists(\App\Observers\PromissoryNoteObserver::class),
            'PromissoryNoteObserver is missing.'
        );
    }

    public function test_app_service_provider_registers_promissory_note_observer()
    {
        $path = dirname(__DIR__) . '/app/Providers/AppServiceProvider.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString('PromissoryNote::observe(PromissoryNoteObserver::class);', $contents);
    }

    public function test_validate_students_controller_has_phase3_methods()
    {
        $controller = new \App\Http\Controllers\ValidateStudentsController();

        foreach (['getFeesForStudent', 'clearForEnrollment'] as $method) {
            $this->assertTrue(method_exists($controller, $method), "ValidateStudentsController missing method: {$method}");
        }

        foreach (['resolveFinancialContext', 'getCollegeFeesForStudent', 'getActiveEnrollment'] as $method) {
            $this->assertTrue(method_exists($controller, $method), "ValidateStudentsController missing helper: {$method}");
        }
    }

    public function test_validate_students_controller_returns_stored_financial_status_for_modal()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/ValidateStudentsController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString("'financial_status' => \$storedFinancialStatus", $contents);
        $this->assertStringContainsString("'workflow_financial_status' => \$workflowFinancialStatus", $contents);
    }

    public function test_validate_students_view_removes_financial_status_badge()
    {
        $path = dirname(__DIR__) . '/resources/views/college/validate_students.blade.php';
        $contents = file_get_contents($path);

        $this->assertStringNotContainsString('Financial:', $contents);
        $this->assertStringContainsString('canClearEnrollment', $contents);
    }

    public function test_clear_for_enrollment_does_not_force_status_paid()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/ValidateStudentsController.php';
        $contents = file_get_contents($path);

        $this->assertStringNotContainsString("'status' => 'PAID'", $contents);
        $this->assertStringContainsString("'cleared_for_enrollment' => true", $contents);
    }

    public function test_compute_financial_status_defaults_to_unpaid_when_no_payments_and_no_promissory_note()
    {
        $model = $this->getMockBuilder(\App\Models\StudentEnrollment::class)
            ->onlyMethods(['activePromissoryNote', 'promissoryNotes', 'payments'])
            ->getMock();

        $model->method('activePromissoryNote')->willReturn(new class {
            public function first() { return null; }
        });

        $model->method('promissoryNotes')->willReturn(new class {
            public function whereIn($column, $values) { return $this; }
            public function where($column, $operator, $value) { return $this; }
            public function orderByDesc($column) { return $this; }
            public function first() { return null; }
        });

        $model->method('payments')->willReturn(new class {
            public function exists() { return false; }
        });

        $this->assertSame('UNPAID', $model->computeFinancialStatus());
    }
}
