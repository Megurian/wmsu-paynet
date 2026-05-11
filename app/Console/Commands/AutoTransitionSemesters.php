<?php

namespace App\Console\Commands;

use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\StudentEnrollment;
use App\Services\PromissoryNoteDelinquencyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoTransitionSemesters extends Command
{
    protected $signature = 'semesters:auto-transition {--as-of= : Optional date to evaluate semester transitions (Y-m-d)}';

    protected $description = 'Automatically end and start semesters according to planned semester dates for academic years with auto transition enabled.';

    public function handle(): int
    {
        $today = $this->option('as-of')
            ? Carbon::parse($this->option('as-of'))->startOfDay()
            : Carbon::today();

        $years = SchoolYear::where('is_active', true)
            ->whereHas('semesters', function ($query) {
                $query->where('is_auto', true);
            })
            ->with(['semesters' => function ($query) {
                $query->orderBy('starts_at')->orderBy('id');
            }])
            ->get();

        $ended = 0;
        $started = 0;

        foreach ($years as $year) {
            $activeSemester = $year->semesters->firstWhere('is_active', true);

            if ($activeSemester && $this->shouldEndSemester($activeSemester, $today)) {
                $this->endSemester($activeSemester);
                $ended++;
            }

            $activeSemester = $year->semesters->firstWhere('is_active', true);

            if (! $activeSemester) {
                $nextSemester = $year->semesters
                    ->where('is_active', false)
                    ->whereNull('ended_at')
                    ->filter(fn (Semester $semester) => $semester->starts_at && $semester->starts_at->lte($today))
                    ->sortBy('starts_at')
                    ->first();

                if ($nextSemester && $nextSemester->is_auto) {
                    $this->startSemester($nextSemester);
                    $started++;
                }
            }
        }

        $this->info(sprintf(
            'Auto semester transition run for %s: %d ended, %d started.',
            $today->toDateString(),
            $ended,
            $started
        ));

        return self::SUCCESS;
    }

    private function shouldEndSemester(Semester $semester, Carbon $today): bool
    {
        return $semester->is_auto
            && $semester->will_end_at !== null
            && $today->gte($semester->will_end_at->copy()->startOfDay());
    }

    private function endSemester(Semester $semester): void
    {
        DB::transaction(function () use ($semester) {
            $semester->update([
                'is_active' => false,
                'ended_at' => now(),
            ]);

            StudentEnrollment::where('school_year_id', $semester->school_year_id)
                ->where('semester_id', $semester->id)
                ->where('cleared_for_enrollment', true)
                ->where('status', StudentEnrollment::PAID)
                ->update(['status' => StudentEnrollment::ENROLLED]);

            // Do not void unresolved FOR_PAYMENT_VALIDATION enrollments at year-end.
            // They must remain payable until cleared.

            app(PromissoryNoteDelinquencyService::class)->processDelinquency(now());

            Log::info('Auto-ended semester', [
                'semester_id' => $semester->id,
                'school_year_id' => $semester->school_year_id,
                'semester_name' => $semester->name,
            ]);
        });
    }

    private function startSemester(Semester $semester): void
    {
        DB::transaction(function () use ($semester) {
            $semester->update([
                'is_active' => true,
                'started_at' => now(),
            ]);

            Log::info('Auto-started semester', [
                'semester_id' => $semester->id,
                'school_year_id' => $semester->school_year_id,
                'semester_name' => $semester->name,
            ]);
        });
    }

    private function isFinalSemester(Semester $semester): bool
    {
        $lastSemester = $semester->schoolYear->semesters
            ->sortBy('starts_at')
            ->last();

        return $lastSemester && $lastSemester->id === $semester->id;
    }
}
