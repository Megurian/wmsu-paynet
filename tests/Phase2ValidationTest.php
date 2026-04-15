<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Phase 1 & 2 Validation Test
 * Tests that Phase 1 migrations and Phase 2 service classes are properly structured
 */
class Phase2ValidationTest extends TestCase
{
    /**
     * Test that Phase 1 migration files exist
     */
    public function test_phase1_migrations_exist()
    {
        $migrations = [
            'database/migrations/2026_04_07_080000_create_promissory_notes_table.php',
            'database/migrations/2026_04_07_080100_create_promissory_note_fees_table.php',
            'database/migrations/2026_04_07_080200_add_promissory_fields_to_payments_table.php',
        ];

        $appRoot = dirname(__DIR__);
        foreach ($migrations as $migration) {
            $path = $appRoot . '/' . $migration;
            $this->assertFileExists($path, "Migration file missing: {$migration}");
        }
    }

    /**
     * Test that Phase 1 model files exist and have proper structure
     */
    public function test_phase1_models_exist()
    {
        $models = [
            'app/Models/PromissoryNote.php',
            'app/Models/Payment.php',
            'app/Models/StudentEnrollment.php',
            'app/Models/Student.php',
        ];

        $appRoot = dirname(__DIR__);
        foreach ($models as $model) {
            $path = $appRoot . '/' . $model;
            $this->assertFileExists($path, "Model file missing: {$model}");
        }
    }

    /**
     * Test that Phase 2 exception classes are properly defined
     */
    public function test_phase2_exceptions_exist()
    {
        $exceptions = [
            'App\Exceptions\PromissoryNoteException',
            'App\Exceptions\PromissoryNoteSettlementException',
            'App\Exceptions\PromissoryNoteAlreadyClosedException',
            'App\Exceptions\PromissoryNotePartialAllocationException',
            'App\Exceptions\PromissoryNoteLockedForUpdateException',
            'App\Exceptions\PromissoryNoteNotSettleableException',
        ];

        foreach ($exceptions as $exception) {
            $this->assertTrue(
                class_exists($exception),
                "Exception class not found: {$exception}"
            );
        }
    }

    /**
     * Test that Phase 2 service classes are properly defined
     */
    public function test_phase2_service_classes_exist()
    {
        $this->assertTrue(
            class_exists('App\Services\PromissoryNoteSettlementService'),
            "Service class not found: App\Services\PromissoryNoteSettlementService"
        );
    }

    /**
     * Test that Phase 2 form request classes exist
     */
    public function test_phase2_form_requests_exist()
    {
        $this->assertTrue(
            class_exists('App\Http\Requests\CollectPromissoryPaymentRequest'),
            "Form request class not found: App\Http\Requests\CollectPromissoryPaymentRequest"
        );
    }

    /**
     * Test that PromissoryNoteSettlementService has required methods
     */
    public function test_settlement_service_has_required_methods()
    {
        $service = new \App\Services\PromissoryNoteSettlementService();

        $this->assertTrue(
            method_exists($service, 'settlePayment'),
            "PromissoryNoteSettlementService missing method: settlePayment"
        );

        $this->assertTrue(
            method_exists($service, 'validateSettleability'),
            "PromissoryNoteSettlementService missing method: validateSettleability"
        );

        $this->assertTrue(
            method_exists($service, 'getSettlementSummary'),
            "PromissoryNoteSettlementService missing method: getSettlementSummary"
        );
    }

    /**
     * Test that PromissoryNote model has required methods
     */
    public function test_promissory_note_model_has_required_methods()
    {
        $methods = [
            'student', 'enrollment', 'fees', 'payments',
            'isActive', 'isPending', 'isVoided', 'isDefaulted', 'isBadDebt', 'isClosed',
            'canSettlePayment', 'isSignatureOverdue',
            'settlePayment', 'void', 'checkForDefault', 'retrieveFromDefault',
            'scopeActive', 'scopePending', 'scopeOverdue', 'scopeForStudent',
        ];

        $reflection = new \ReflectionClass('App\Models\PromissoryNote');

        foreach ($methods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "PromissoryNote model missing method: {$method}"
            );
        }
    }

    /**
     * Test that Payment model has PN-related methods
     */
    public function test_payment_model_has_pn_methods()
    {
        $methods = ['promissoryNote', 'isPromissorySettlement', 'isCashPayment', 'scopePromissory', 'scopeCash'];

        $reflection = new \ReflectionClass('App\Models\Payment');

        foreach ($methods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "Payment model missing method: {$method}"
            );
        }
    }

    /**
     * Test that StudentEnrollment model has PN-related methods
     */
    public function test_student_enrollment_model_has_pn_methods()
    {
        $methods = [
            'promissoryNotes', 'activePromissoryNote',
            'computeFinancialStatus',
            'scopeFinanciallyCleared', 'scopeFinanciallyDeferred',
        ];

        $reflection = new \ReflectionClass('App\Models\StudentEnrollment');

        foreach ($methods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "StudentEnrollment model missing method: {$method}"
            );
        }
    }

    /**
     * Test that Student model has PN-related methods
     */
    public function test_student_model_has_pn_methods()
    {
        $methods = [
            'promissoryNotes', 'hasUnpaidPriorNote',
            'scopeBlockedFromNextSemester',
        ];

        $reflection = new \ReflectionClass('App\Models\Student');

        foreach ($methods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "Student model missing method: {$method}"
            );
        }
    }

    /**
     * Test that controllers have Phase 2 methods
     */
    public function test_treasurer_cashiering_controller_has_pn_methods()
    {
        $methods = [
            'getPromissoryNotes', 'collectPayment', 'collectCashPayment', 'collectPromissoryPayment'
        ];

        $reflection = new \ReflectionClass('App\Http\Controllers\TreasurerCashieringController');

        foreach ($methods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "TreasurerCashieringController missing method: {$method}"
            );
        }
    }

    /**
     * Test that org controller has Phase 2 methods
     */
    public function test_org_payment_controller_has_pn_methods()
    {
        $methods = [
            'getPromissoryNotes', 'collectPayment', 'collectCashPayment', 'collectPromissoryPayment'
        ];

        $reflection = new \ReflectionClass('App\Http\Controllers\OrganizationPaymentController');

        foreach ($methods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "OrganizationPaymentController missing method: {$method}"
            );
        }
    }

    /**
     * Test that routes are properly configured
     */
    public function test_routes_are_configured()
    {
        // Read routes file and check for PN endpoints
        $appRoot = dirname(__DIR__);
        $routesPath = $appRoot . '/routes/web.php';
        $routesContent = file_get_contents($routesPath);

        $this->assertStringContainsString(
            'promissory-notes',
            $routesContent,
            "Routes file missing promissory-notes endpoint"
        );

        $this->assertStringContainsString(
            'getPromissoryNotes',
            $routesContent,
            "Routes file missing getPromissoryNotes call"
        );
    }

    /**
     * Test exception inheritance chain
     */
    public function test_exception_inheritance()
    {
        // Child exceptions only (excluding the base PromissoryNoteSettlementException)
        $childExceptions = [
            'App\Exceptions\PromissoryNoteAlreadyClosedException',
            'App\Exceptions\PromissoryNotePartialAllocationException',
            'App\Exceptions\PromissoryNoteLockedForUpdateException',
            'App\Exceptions\PromissoryNoteNotSettleableException',
        ];

        foreach ($childExceptions as $exception) {
            $this->assertTrue(
                is_subclass_of($exception, 'App\Exceptions\PromissoryNoteSettlementException'),
                "{$exception} should extend PromissoryNoteSettlementException"
            );
        }
        
        // Base exception should extend PromissoryNoteException
        $this->assertTrue(
            is_subclass_of('App\Exceptions\PromissoryNoteSettlementException', 'App\Exceptions\PromissoryNoteException'),
            "PromissoryNoteSettlementException should extend PromissoryNoteException"
        );
    }

    /**
     * Test Payment model has payment_type fillable
     */
    public function test_payment_model_fillable_includes_pn_fields()
    {
        $reflection = new \ReflectionClass('App\Models\Payment');
        $property = $reflection->getProperty('fillable');
        $property->setAccessible(true);

        // Create a dummy instance to check fillable
        // We can't instantiate without a database, so we'll check the file content
        $appRoot = dirname(__DIR__);
        $paymentPath = $appRoot . '/app/Models/Payment.php';
        $content = file_get_contents($paymentPath);

        $this->assertStringContainsString(
            'payment_type',
            $content,
            "Payment model should have payment_type in fillable"
        );

        $this->assertStringContainsString(
            'promissory_note_id',
            $content,
            "Payment model should have promissory_note_id in fillable"
        );
    }
}
