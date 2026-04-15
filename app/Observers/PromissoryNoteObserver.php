<?php

namespace App\Observers;

use App\Models\PromissoryNote;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\Log;

class PromissoryNoteObserver
{
    public function saved(PromissoryNote $promissoryNote): void
    {
        $this->syncEnrollmentFinancialStatus($promissoryNote);
    }

    public function deleted(PromissoryNote $promissoryNote): void
    {
        $this->syncEnrollmentFinancialStatus($promissoryNote);
    }

    public function forceDeleted(PromissoryNote $promissoryNote): void
    {
        $this->syncEnrollmentFinancialStatus($promissoryNote);
    }

    private function syncEnrollmentFinancialStatus(PromissoryNote $promissoryNote): void
    {
        /** @var StudentEnrollment|null $enrollment */
        $enrollment = StudentEnrollment::find($promissoryNote->enrollment_id);

        if (! $enrollment) {
            Log::warning('PromissoryNoteObserver: enrollment not found for promissory note', [
                'promissory_note_id' => $promissoryNote->id,
                'enrollment_id' => $promissoryNote->enrollment_id,
            ]);
            return;
        }

        $enrollment->refreshFinancialStatus();
    }
}