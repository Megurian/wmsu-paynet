<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\StudentEnrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class OSASetupController extends Controller
{
    public function edit()
    {
        return view('osa.setup', $this->setupViewData());
    }

    public function createAcademicSetup()
    {
        return view('osa.create-academic-setup', $this->setupViewData());
    }

    private function setupViewData(): array
    {
        $schoolYears = SchoolYear::with('semesters')->orderBy('sy_start', 'desc')->get();
        $latestSchoolYear = SchoolYear::where('is_active', true)->with('semesters')->first();

        $existingSemesters = [];
        if ($latestSchoolYear) {
            $existingSemesters = $latestSchoolYear->semesters->pluck('name')->toArray();
        }

        return compact('schoolYears', 'latestSchoolYear', 'existingSemesters');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sy_start' => 'required|date',
            'sy_end' => 'required|date|after_or_equal:sy_start',
            'semester_preset' => 'required|in:semestral,trimester,quadmester,custom',
            'include_summer' => 'nullable|boolean',
            'custom_semester_count' => 'nullable|integer|min:1|max:12',
            'semester_lengths' => 'nullable|array',
            'semester_lengths.*' => 'nullable|integer|min:1',
            'semester_starts_at' => 'required|array',
            'semester_starts_at.*' => 'required|date',
            'semester_will_end_at' => 'required|array',
            'semester_will_end_at.*' => 'required|date',
            'custom_semester_weights' => 'nullable|array',
            'custom_semester_weights.*' => 'nullable|integer|min:1',
        ]);

        $schoolYearStart = Carbon::parse($validated['sy_start'])->startOfDay();
        $schoolYearEnd = Carbon::parse($validated['sy_end'])->startOfDay();

        if ($schoolYearStart->year === $schoolYearEnd->year) {
            return back()->withErrors([
                'sy_end' => 'Start and end dates must span different calendar years (e.g., 2024–2025).',
            ])->withInput();
        }

        $activeSY = SchoolYear::where('is_active', true)->with('semesters')->first();
        if ($activeSY && $activeSY->semesters->contains('is_active', true)) {
            return back()->withErrors([
                'sy_start' => 'Cannot create a new academic year while a semester is currently active. Please end the active semester first.',
            ])->withInput();
        }

        $exists = SchoolYear::where(function ($query) use ($validated) {
            $query->whereBetween('sy_start', [$validated['sy_start'], $validated['sy_end']])
                ->orWhereBetween('sy_end', [$validated['sy_start'], $validated['sy_end']])
                ->orWhere(function ($innerQuery) use ($validated) {
                    $innerQuery->where('sy_start', '<=', $validated['sy_start'])
                        ->where('sy_end', '>=', $validated['sy_end']);
                });
        })->exists();

        if ($exists) {
            return back()->withErrors([
                'sy_start' => 'The school year overlaps with an existing school year.',
            ])->withInput();
        }

        try {
            $semesterLengths = $validated['semester_lengths'] ?? $validated['custom_semester_weights'] ?? [];

            $suggestedSemesterPlan = $this->buildSemesterPlan(
                $schoolYearStart,
                $schoolYearEnd,
                $validated['semester_preset'],
                $request->boolean('include_summer'),
                (int) ($validated['custom_semester_count'] ?? 0),
                $semesterLengths
            );

            $semesterPlan = $this->resolveSemesterPlan(
                $suggestedSemesterPlan,
                $validated['semester_starts_at'],
                $validated['semester_will_end_at'],
                $schoolYearStart,
                $schoolYearEnd
            );
        } catch (\Throwable $e) {
            return back()->withErrors([
                'semester_preset' => $e->getMessage(),
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $semesterPlan, $activeSY) {
                if ($activeSY) {
                    $activeSY->update(['is_active' => false]);
                    $activeSY->semesters()->where('is_active', true)->update(['is_active' => false, 'ended_at' => now()]);
                }

                $newSchoolYear = SchoolYear::create([
                    'sy_start' => $validated['sy_start'],
                    'sy_end' => $validated['sy_end'],
                    'is_active' => true,
                ]);

                foreach ($semesterPlan as $semesterData) {
                    Semester::create([
                        'school_year_id' => $newSchoolYear->id,
                        'name' => $semesterData['name'],
                        'starts_at' => $semesterData['starts_at'],
                        'will_end_at' => $semesterData['will_end_at'],
                        'is_active' => false,
                        'started_at' => null,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create School Year: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['db' => 'Failed to create new school year. Please try again.'])->withInput();
        }

        return redirect()->back()->with('status', 'New School Year added and activated successfully!');
    }

    public function addSemester(Request $request, $schoolYearId)
    {
        $request->validate([
            'semester' => 'required|string',
            'starts_at' => 'required|date',
            'will_end_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $semesterName = $this->canonicalSemesterName($request->semester);

        if (!in_array($semesterName, ['1st SEMESTER', '2nd SEMESTER', 'SUMMER'], true)) {
            return back()->withErrors([
                'semester' => 'Please choose a valid semester name.',
            ])->withInput();
        }

        $schoolYear = SchoolYear::findOrFail($schoolYearId);
        $semesterStart = Carbon::parse($request->starts_at)->startOfDay();
        $semesterEnd = Carbon::parse($request->will_end_at)->startOfDay();
        $schoolYearStart = Carbon::parse($schoolYear->sy_start)->startOfDay();
        $schoolYearEnd = Carbon::parse($schoolYear->sy_end)->startOfDay();

        if ($semesterStart->lt($schoolYearStart) || $semesterEnd->gt($schoolYearEnd)) {
            return back()->withErrors([
                'starts_at' => 'Semester dates must stay within the active school year boundaries.',
            ])->withInput();
        }

        if ($schoolYear->semesters()->where('name', $semesterName)->exists()) {
            return back()->withErrors([
                'semester' => 'A semester with that name already exists in this school year.',
            ])->withInput();
        }

        $semesterOverlaps = $schoolYear->semesters()
            ->whereNotNull('starts_at')
            ->whereNotNull('will_end_at')
            ->where(function ($query) use ($semesterStart, $semesterEnd) {
                $query->whereBetween('starts_at', [$semesterStart->toDateString(), $semesterEnd->toDateString()])
                    ->orWhereBetween('will_end_at', [$semesterStart->toDateString(), $semesterEnd->toDateString()])
                    ->orWhere(function ($innerQuery) use ($semesterStart, $semesterEnd) {
                        $innerQuery->where('starts_at', '<=', $semesterStart->toDateString())
                            ->where('will_end_at', '>=', $semesterEnd->toDateString());
                    });
            })
            ->exists();

        if ($semesterOverlaps) {
            return back()->withErrors([
                'starts_at' => 'Semester dates overlap with an existing semester in this school year.',
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($schoolYear, $request, $semesterName) {
                $schoolYear->semesters()
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'ended_at' => now()]);
                Semester::create([
                    'school_year_id' => $schoolYear->id,
                    'name' => $semesterName,
                    'starts_at' => $request->starts_at,
                    'will_end_at' => $request->will_end_at,
                    'is_active' => true,
                ]);
                $schoolYear->update(['is_active' => true]);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to add semester: ' . $e->getMessage(), ['exception' => $e, 'school_year_id' => $schoolYearId]);
            return back()->withErrors(['db' => 'Failed to add semester. Please try again.'])->withInput();
        }

        return redirect()->back()->with('status', 'Semester added and activated successfully!');
    }

    public function endSemester($schoolYearId)
    {
        $schoolYear = SchoolYear::with('semesters')->findOrFail($schoolYearId);
        $activeSemester = $schoolYear->semesters()
            ->where('is_active', true)
            ->first();

        if (!$activeSemester) {
            return back()->withErrors('No active semester to end.');
        }

        try {
            DB::transaction(function () use ($schoolYear, $activeSemester) {
                // End the current semester
                $activeSemester->update([
                    'is_active' => false,
                    'ended_at' => now(),
                ]);

                // End-of-term auto-enrollment: paid and cleared students become officially enrolled.
                StudentEnrollment::where('school_year_id', $schoolYear->id)
                    ->where('semester_id', $activeSemester->id)
                    ->where('cleared_for_enrollment', true)
                    ->where('status', StudentEnrollment::PAID)
                    ->update(['status' => StudentEnrollment::ENROLLED]);

                // End-of-academic-year cleanup: void stale FOR_PAYMENT_VALIDATION records when the final semester closes.
                if ($this->isFinalSemester($schoolYear, $activeSemester)) {
                    StudentEnrollment::where('school_year_id', $schoolYear->id)
                        ->where('status', StudentEnrollment::FOR_PAYMENT_VALIDATION)
                        ->where('is_void', false)
                        ->update(['is_void' => true]);
                }

                // Trigger delinquency processing when semester ends
                $delinquencyService = app(\App\Services\PromissoryNoteDelinquencyService::class);
                $delinquencyService->processDelinquency(now());

                Log::info('Semester ended', [
                    'school_year_id' => $schoolYear->id,
                    'semester_id' => $activeSemester->id,
                    'semester_name' => $activeSemester->name,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to end semester: ' . $e->getMessage(), ['exception' => $e, 'school_year_id' => $schoolYearId]);
            return back()->withErrors(['db' => 'Failed to end semester. Please try again.']);
        }

        return back()->with('status', 'Semester ended successfully.');
    }

    public function startSemester($schoolYearId, $semesterId)
    {
        $schoolYear = SchoolYear::with('semesters')->findOrFail($schoolYearId);
        $semester = $schoolYear->semesters()->findOrFail($semesterId);

        if ($semester->is_active) {
            return back()->withErrors('This semester is already active.');
        }

        // Check if there's another active semester in this school year
        $activeSemester = $schoolYear->semesters()->where('is_active', true)->first();
        if ($activeSemester) {
            return back()->withErrors('Another semester is already active. Please end it before starting a new one.');
        }

        try {
            DB::transaction(function () use ($semester) {
                $semester->update([
                    'is_active' => true,
                    'started_at' => now(),
                ]);

                Log::info('Semester started', [
                    'semester_id' => $semester->id,
                    'semester_name' => $semester->name,
                    'school_year_id' => $semester->school_year_id,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to start semester: ' . $e->getMessage(), ['exception' => $e, 'semester_id' => $semesterId]);
            return back()->withErrors(['db' => 'Failed to start semester. Please try again.']);
        }

        return back()->with('status', 'Semester started successfully.');
    }

    private function isFinalSemester(SchoolYear $schoolYear, Semester $semester): bool
    {
        $lastSemester = $schoolYear->semesters()
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get()
            ->last();

        return $lastSemester && $lastSemester->id === $semester->id;
    }

    private function buildSemesterPlan(
        Carbon $schoolYearStart,
        Carbon $schoolYearEnd,
        string $preset,
        bool $includeSummer,
        int $customSemesterCount,
        array $semesterLengths
    ): array {
        $weights = $this->resolveSemesterWeights(
            $preset,
            $includeSummer,
            $customSemesterCount,
            $semesterLengths
        );

        if (empty($weights)) {
            throw new \RuntimeException('At least one semester must be defined for the selected preset.');
        }

        $totalDays = $schoolYearStart->diffInDays($schoolYearEnd) + 1;

        if ($totalDays < count($weights)) {
            throw new \RuntimeException('The academic year must be long enough to fit the selected semester structure.');
        }

        $labels = $this->resolveSemesterLabels($preset, count($weights), $includeSummer);
        $allocations = $this->allocateSemesterDays($totalDays, $weights);

        $plan = [];
        $currentStart = $schoolYearStart->copy();

        foreach ($allocations as $index => $days) {
            $currentEnd = $index === count($allocations) - 1
                ? $schoolYearEnd->copy()
                : $currentStart->copy()->addDays($days - 1);

            $plan[] = [
                'name' => $labels[$index],
                'starts_at' => $currentStart->toDateString(),
                'will_end_at' => $currentEnd->toDateString(),
            ];

            $currentStart = $currentEnd->copy()->addDay();
        }

        return $plan;
    }

    private function resolveSemesterPlan(
        array $suggestedPlan,
        array $semesterStartsAt,
        array $semesterWillEndAt,
        Carbon $schoolYearStart,
        Carbon $schoolYearEnd
    ): array {
        if (count($semesterStartsAt) !== count($suggestedPlan) || count($semesterWillEndAt) !== count($suggestedPlan)) {
            throw new \RuntimeException('Please provide a start date and end date for every semester.');
        }

        $plan = [];
        $previousEndDate = null;

        foreach ($suggestedPlan as $index => $semesterData) {
            $semesterStart = Carbon::parse($semesterStartsAt[$index])->startOfDay();
            $semesterEnd = Carbon::parse($semesterWillEndAt[$index])->startOfDay();

            if ($semesterStart->gt($semesterEnd)) {
                throw new \RuntimeException('Semester start dates must be earlier than their end dates.');
            }

            if ($semesterStart->lt($schoolYearStart) || $semesterEnd->gt($schoolYearEnd)) {
                throw new \RuntimeException('Semester dates must stay within the selected academic-year span.');
            }

            if ($previousEndDate && $semesterStart->lte($previousEndDate)) {
                throw new \RuntimeException('Semester dates must stay in chronological order and must not overlap.');
            }

            $plan[] = [
                'name' => $semesterData['name'],
                'starts_at' => $semesterStart->toDateString(),
                'will_end_at' => $semesterEnd->toDateString(),
            ];

            $previousEndDate = $semesterEnd;
        }

        return $plan;
    }

    private function resolveSemesterWeights(
        string $preset,
        bool $includeSummer,
        int $customSemesterCount,
        array $semesterLengths
    ): array {
        $defaultWeights = match ($preset) {
            'semestral' => $includeSummer ? [5, 5, 1] : [5, 5],
            'trimester' => [4, 4, 4],
            'quadmester' => [3, 3, 3, 3],
            'custom' => $this->defaultCustomSemesterWeights($customSemesterCount),
            default => throw new \RuntimeException('Unknown semester preset selected.'),
        };

        $normalizedLengths = $this->normalizeSemesterLengths($semesterLengths);

        if (empty($normalizedLengths)) {
            return $defaultWeights;
        }

        if (count($normalizedLengths) !== count($defaultWeights)) {
            throw new \RuntimeException('The number of semester lengths must match the selected semester structure.');
        }

        return $normalizedLengths;
    }

    private function defaultCustomSemesterWeights(int $customSemesterCount): array
    {
        if ($customSemesterCount <= 0) {
            throw new \RuntimeException('Please enter how many semesters to create for the custom preset.');
        }

        return array_fill(0, $customSemesterCount, 1);
    }

    private function resolveSemesterLabels(string $preset, int $count, bool $includeSummer): array
    {
        if ($preset === 'semestral') {
            return $includeSummer ? ['1st SEMESTER', '2nd SEMESTER', 'SUMMER'] : ['1st SEMESTER', '2nd SEMESTER'];
        }

        $labels = [];
        for ($index = 1; $index <= $count; $index++) {
            $labels[] = $this->ordinalLabel($index) . ' SEMESTER';
        }

        return $labels;
    }

    private function normalizeSemesterLengths(array $semesterLengths): array
    {
        if ($semesterLengths === []) {
            return [];
        }

        $normalized = [];

        foreach ($semesterLengths as $semesterLength) {
            if ($semesterLength === null || $semesterLength === '') {
                continue;
            }

            $length = (int) $semesterLength;

            if ($length <= 0) {
                throw new \RuntimeException('Semester lengths must be positive numbers.');
            }

            $normalized[] = $length;
        }

        return $normalized;
    }

    private function canonicalSemesterName(string $semester): string
    {
        $normalized = strtolower(trim($semester));

        if ($normalized === 'summer' || $normalized === 'summer semester') {
            return 'SUMMER';
        }

        if (preg_match('/^(\d+)(st|nd|rd|th)?(?:\s+semester)?$/i', $normalized, $matches)) {
            $number = (int) $matches[1];

            return $number . $this->ordinalSuffix($number) . ' SEMESTER';
        }

        return strtoupper(trim($semester));
    }

    private function allocateSemesterDays(int $totalDays, array $weights): array
    {
        $weightTotal = array_sum($weights);
        $remainingDays = $totalDays;
        $remainingWeight = $weightTotal;
        $allocations = [];

        foreach ($weights as $index => $weight) {
            $semestersRemaining = count($weights) - $index;

            if ($semestersRemaining === 1) {
                $days = $remainingDays;
            } else {
                $idealDays = (int) floor(($remainingDays * $weight) / $remainingWeight);
                $minimumDaysNeededForRest = $semestersRemaining - 1;
                $days = max(1, min($idealDays, $remainingDays - $minimumDaysNeededForRest));
            }

            $allocations[] = $days;
            $remainingDays -= $days;
            $remainingWeight -= $weight;
        }

        return $allocations;
    }

    private function ordinalLabel(int $number): string
    {
        if (in_array($number % 100, [11, 12, 13], true)) {
            return $number . 'th';
        }

        return match ($number % 10) {
            1 => $number . 'st',
            2 => $number . 'nd',
            3 => $number . 'rd',
            default => $number . 'th',
        };
    }

    private function ordinalSuffix(int $number): string
    {
        if (in_array($number % 100, [11, 12, 13], true)) {
            return 'th';
        }

        return match ($number % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
