<?php

namespace App\Console\Commands;

use App\Models\PromissoryNote;
use App\Notifications\PromissoryNoteUnsignedExpiredNotification;
use App\Services\PromissoryNoteIssuanceService;
use Illuminate\Console\Command;

class CheckSignatureDeadlineCommand extends Command
{
    protected $signature = 'promissory-notes:check-signature-deadline';

    protected $description = 'Void expired unsigned promissory notes and notify students.';

    public function handle(PromissoryNoteIssuanceService $issuanceService): int
    {
        $voidedCount = 0;

        PromissoryNote::pending()
            ->where('signature_deadline', '<', now())
            ->with(['student', 'enrollment'])
            ->orderBy('id')
            ->chunkById(100, function ($notes) use (&$voidedCount, $issuanceService) {
                foreach ($notes as $note) {
                    try {
                        if (! $issuanceService->checkSignatureDeadline($note, now())) {
                            continue;
                        }

                        $voidedCount++;

                        $note->refresh()->load(['student', 'enrollment']);

                        if ($note->student?->email) {
                            $note->student->notify(new PromissoryNoteUnsignedExpiredNotification($note));
                        }
                    } catch (\Throwable $e) {
                        report($e);
                        $this->error("Failed processing promissory note {$note->id}: {$e->getMessage()}");
                        continue;
                    }
                }
            });

        $this->info("Voided {$voidedCount} expired unsigned promissory note(s).");

        return self::SUCCESS;
    }
}