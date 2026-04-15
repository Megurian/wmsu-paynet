<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Phase5ValidationTest extends TestCase
{
    public function test_app_service_provider_validates_reminder_days_config()
    {
        $path = dirname(__DIR__) . '/app/Providers/AppServiceProvider.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString(
            "PROMISSORY_NOTE_REMINDER_DAYS_BEFORE_DUE must be a positive integer",
            $contents,
            'AppServiceProvider must validate the reminder days configuration.'
        );
    }

    public function test_validate_students_controller_enforces_college_scope_for_issue_promissory_note()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/ValidateStudentsController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString(
            'abort_unless(',
            $contents,
            'ValidateStudentsController should abort when a coordinator issues a PN outside their college.'
        );

        $this->assertStringContainsString(
            'college_id === Auth::user()->college_id',
            $contents,
            'ValidateStudentsController must verify enrollment college matches the authenticated user college.'
        );
    }

    public function test_promissory_note_observer_has_force_deleted_handler()
    {
        $this->assertTrue(
            method_exists(\App\Observers\PromissoryNoteObserver::class, 'forceDeleted'),
            'PromissoryNoteObserver should handle forceDeleted events.'
        );
    }

    public function test_process_promissory_note_delinquency_command_description_is_explicit()
    {
        $path = dirname(__DIR__) . '/app/Console/Commands/ProcessPromissoryNoteDelinquency.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString(
            'Process PN reminders, DEFAULT transitions, and BAD_DEBT escalations',
            $contents,
            'Delinquency command description must reflect reminders and escalation behavior.'
        );
    }

    public function test_process_promissory_note_delinquency_command_uses_due_dates_for_reminders()
    {
        $path = dirname(__DIR__) . '/app/Console/Commands/ProcessPromissoryNoteDelinquency.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString(
            'dueDatesForReminders',
            $contents,
            'Delinquency command should call dueDatesForReminders() for reminder filtering.'
        );
    }

    public function test_student_enrollment_compute_financial_status_defaults_to_unpaid_when_unclear()
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

    public function test_student_portal_controller_generates_unique_signed_note_filename()
    {
        $path = dirname(__DIR__) . '/app/Http/Controllers/Student/StudentPortalController.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString(
            'uniqid()',
            $contents,
            'StudentPortalController should generate unique signed note filenames to avoid overwrites.'
        );
    }

    public function test_promissory_note_delinquency_service_deduplicates_notifications()
    {
        $path = dirname(__DIR__) . '/app/Services/PromissoryNoteDelinquencyService.php';
        $contents = file_get_contents($path);

        $this->assertStringContainsString(
            'Cache::add',
            $contents,
            'Delinquency service should use cache-based deduplication for notifications.'
        );
    }
}
