<?php

namespace App\Console\Commands;

use App\Models\PromissoryNote;
use App\Services\PromissoryNoteDelinquencyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPromissoryNoteDelinquency extends Command
{
    protected $signature = 'promissory-notes:process-delinquency {--as-of= : Optional date for backfill/testing}';

    protected $description = 'Process PN reminders, DEFAULT transitions, and BAD_DEBT escalations. Use --as-of for backfill.';

    public function handle(PromissoryNoteDelinquencyService $delinquencyService): int
    {
        $currentDate = $this->option('as-of')
            ? Carbon::parse($this->option('as-of'))
            : now();

        $summary = $delinquencyService->processDelinquency($currentDate);

        $this->info(sprintf(
            'Processed PN delinquency for %s: %d reminder(s), %d defaulted, %d marked bad debt.',
            $currentDate->toDateTimeString(),
            $summary['reminders_sent'],
            $summary['defaulted'],
            $summary['bad_debt']
        ));

        return self::SUCCESS;
    }
}