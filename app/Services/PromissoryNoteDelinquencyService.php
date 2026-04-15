<?php

namespace App\Services;

use App\Models\PromissoryNote;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Notifications\PromissoryNoteDefaultNotification;
use App\Notifications\PromissoryNoteReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromissoryNoteDelinquencyService
{
    public function evaluateDelinquency(PromissoryNote $note, Carbon $currentDate, array $policy = []): array
    {
        $result = [
            'transitioned' => false,
            'from_status' => $note->status,
            'to_status' => $note->status,
            'notified' => false,
        ];

        if ($note->isActive() && ($policy['semester_ended'] ?? false) && $note->due_date && $note->due_date->lt($currentDate)) {
            $defaultAudit = null;

            DB::transaction(function () use ($note, $currentDate, &$result, &$defaultAudit) {
                $lockedNote = PromissoryNote::whereKey($note->id)->lockForUpdate()->first();

                if (! $lockedNote || ! $lockedNote->isActive() || ! $lockedNote->due_date || ! $lockedNote->due_date->lt($currentDate)) {
                    return;
                }

                $lockedNote->markDefault();

                $result['transitioned'] = true;
                $result['to_status'] = PromissoryNote::STATUS_DEFAULT;

                $defaultAudit = [
                    'promissory_note_id' => $lockedNote->id,
                    'student_id' => $lockedNote->student_id,
                    'from_status' => $result['from_status'],
                    'to_status' => $result['to_status'],
                    'as_of' => $currentDate->toDateString(),
                ];
            });

            if ($defaultAudit) {
                Log::info('Promissory note transitioned to DEFAULT', $defaultAudit);
            }
        }

        if ($note->isDefaulted() && ! empty($policy['bad_debt_ready'])) {
            $badDebtAudit = null;

            $schoolYearEnd = $note->enrollment?->schoolYear?->sy_end;
            if (! $schoolYearEnd || $currentDate->isBefore($schoolYearEnd)) {
                return $result;
            }

            if (! $note->default_date || $note->default_date->isSameDay($currentDate)) {
                return $result;
            }

            DB::transaction(function () use ($note, $currentDate, &$result, &$badDebtAudit) {
                $lockedNote = PromissoryNote::whereKey($note->id)->lockForUpdate()->first();

                if (! $lockedNote || ! $lockedNote->isDefaulted()) {
                    return;
                }

                $lockedNote->markBadDebt();

                $result['transitioned'] = true;
                $result['to_status'] = PromissoryNote::STATUS_BAD_DEBT;

                $badDebtAudit = [
                    'promissory_note_id' => $lockedNote->id,
                    'student_id' => $lockedNote->student_id,
                    'from_status' => $result['from_status'],
                    'to_status' => $result['to_status'],
                    'as_of' => $currentDate->toDateString(),
                ];
            });

            if ($badDebtAudit) {
                Log::info('Promissory note transitioned to BAD_DEBT', $badDebtAudit);
            }
        }

        return $result;
    }

    public function sendReminder(PromissoryNote $note, int $daysBeforeDue): void
    {
        if (! $note->student || ! $note->student->email) {
            Log::warning('Promissory note reminder skipped due to missing student or email.', [
                'promissory_note_id' => $note->id,
                'student_id' => $note->student?->id,
            ]);
            return;
        }

        $cacheKey = sprintf('promissory_note_reminder_sent:%s:%s:%s', $note->id, $note->student_id, $daysBeforeDue);
        if (! Cache::add($cacheKey, true, now()->endOfDay())) {
            return;
        }

        $note->student->notify(new PromissoryNoteReminderNotification($note, $daysBeforeDue));
    }

    public function sendStatusNotification(PromissoryNote $note): void
    {
        if (! $note->student || ! $note->student->email) {
            Log::warning('Promissory note status notification skipped due to missing student or email.', [
                'promissory_note_id' => $note->id,
                'student_id' => $note->student?->id,
                'status' => $note->status,
            ]);
            return;
        }

        $stage = $note->isBadDebt() ? PromissoryNote::STATUS_BAD_DEBT : PromissoryNote::STATUS_DEFAULT;

        $note->student->notify(new PromissoryNoteDefaultNotification($note, $stage));
    }

    public function shouldDefaultNotes(Carbon $currentDate): bool
    {
        $semesterDeadline = $this->currentSemesterDeadline();

        return $semesterDeadline ? $currentDate->greaterThanOrEqualTo($semesterDeadline) : false;
    }

    public function shouldPromoteToBadDebt(Carbon $currentDate): bool
    {
        $activeSemester = Semester::with('schoolYear')
            ->where('is_active', true)
            ->first();

        if (!$activeSemester || !$activeSemester->schoolYear) {
            return false;
        }

        return $currentDate->greaterThanOrEqualTo(
            $activeSemester->schoolYear->sy_end
        );
    }

    public function dueDatesForReminders(Carbon $currentDate): array
    {
        $daysBeforeDue = $this->reminderDaysBeforeDue();

        return [
            $currentDate->copy()->addDays($daysBeforeDue)->toDateString(),
            $currentDate->toDateString(),
        ];
    }

    public function reminderDaysBeforeDue(): int
    {
        return (int) config('app.promissory_note_reminder_days_before_due', 7);
    }

    public function currentSemesterDeadline(): ?Carbon
    {
        $activeSemester = Semester::with('schoolYear')->where('is_active', true)->first();

        if ($activeSemester) {
            return $activeSemester->effectiveEndDate();
        }

        $latestClosedSemester = Semester::with('schoolYear')
            ->whereNotNull('ended_at')
            ->orderByDesc('ended_at')
            ->first();

        return $latestClosedSemester?->effectiveEndDate();
    }

    /**
     * Process all PN delinquency checks: reminders, DEFAULT transitions, and BAD_DEBT escalations.
     * Called by the scheduler (console command) or manually by OSA when ending a semester.
     *
     * @param Carbon $currentDate Date to evaluate delinquency against (default: now)
     * @return array Summary of actions: ['reminders_sent' => int, 'defaulted' => int, 'bad_debt' => int]
     */
    public function processDelinquency(Carbon $currentDate): array
    {
        $summary = [
            'reminders_sent' => 0,
            'defaulted' => 0,
            'bad_debt' => 0,
        ];

        $policy = [
            'semester_ended' => $this->shouldDefaultNotes($currentDate),
            'bad_debt_ready' => $this->shouldPromoteToBadDebt($currentDate),
        ];

        $reminderDates = $this->dueDatesForReminders($currentDate);

        // Process ACTIVE notes for reminders and DEFAULT transitions
        PromissoryNote::active()
            ->where('remaining_balance', '>', 0)
            ->where(function ($query) use ($reminderDates, $policy, $currentDate) {
                $query->where(function ($query) use ($reminderDates) {
                    $query->whereDate('due_date', $reminderDates[0])
                        ->orWhereDate('due_date', $reminderDates[1]);
                });

                if ($policy['semester_ended']) {
                    $query->orWhereDate('due_date', '<', $currentDate->toDateString());
                }
            })
            ->with(['student', 'enrollment'])
            ->orderBy('id')
            ->chunkById(100, function ($notes) use (&$summary, $currentDate, $policy) {
                foreach ($notes as $note) {
                    // Check for DEFAULT transition
                    if ($policy['semester_ended'] && $note->due_date && $note->due_date->lt($currentDate)) {
                        $result = $this->evaluateDelinquency($note, $currentDate, $policy);

                        if ($result['transitioned'] && $result['to_status'] === PromissoryNote::STATUS_DEFAULT) {
                            $summary['defaulted']++;

                            $note->refresh()->loadMissing(['student', 'enrollment']);
                            $this->sendStatusNotification($note);
                        }
                        continue;
                    }

                    // Send reminder if student exists
                    if (! $note->student) {
                        continue;
                    }

                    $this->sendReminder($note, $this->reminderDaysBeforeDue());
                    $summary['reminders_sent']++;
                }
            });

        // Process BAD_DEBT escalations
        if ($policy['bad_debt_ready']) {
            PromissoryNote::defaulted()
                ->where('remaining_balance', '>', 0)
                ->with(['student', 'enrollment'])
                ->orderBy('id')
                ->chunkById(100, function ($notes) use (&$summary, $currentDate, $policy) {
                    foreach ($notes as $note) {
                        $result = $this->evaluateDelinquency($note, $currentDate, $policy);

                        if ($result['transitioned'] && $result['to_status'] === PromissoryNote::STATUS_BAD_DEBT) {
                            $summary['bad_debt']++;

                            $note->refresh()->loadMissing(['student', 'enrollment']);
                            $this->sendStatusNotification($note);
                        }
                    }
                });
        }

        Log::info('Processed promissory note delinquency', [
            'processed_for' => $currentDate->toDateString(),
            'reminders_sent' => $summary['reminders_sent'],
            'defaulted' => $summary['defaulted'],
            'bad_debt' => $summary['bad_debt'],
        ]);

        return $summary;
    }
}