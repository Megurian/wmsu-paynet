<?php

namespace App\Services;

use App\Models\PromissoryNote;
use App\Models\SchoolYear;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PromissoryNoteReportingService
{
    public function buildCollegeDashboard(int $collegeId, ?int $schoolYearId, ?int $semesterId): array
    {
        $baseQuery = $this->collegeScopeQuery($collegeId, $schoolYearId, $semesterId);

        $summaryQuery = clone $baseQuery;

        $totalOriginalAmount = (float) (clone $summaryQuery)->sum('original_amount');
        $totalRemainingBalance = (float) (clone $summaryQuery)->sum('remaining_balance');

        $summary = [
            'issued_count' => (clone $summaryQuery)->count(),
            'active_count' => (clone $summaryQuery)->where('status', PromissoryNote::STATUS_ACTIVE)->count(),
            'closed_count' => (clone $summaryQuery)->closed()->count(),
            'voided_count' => (clone $summaryQuery)->voided()->count(),
            'overdue_count' => (clone $summaryQuery)->overdue()->count(),
            'defaulted_count' => (clone $summaryQuery)->defaulted()->count(),
            'bad_debt_count' => (clone $summaryQuery)->badDebt()->count(),
            'pending_signature_count' => (clone $summaryQuery)->pending()->count(),
            'pending_verification_count' => (clone $summaryQuery)->pendingVerification()->count(),
            'total_original_amount' => $totalOriginalAmount,
            'total_remaining_balance' => $totalRemainingBalance,
            'total_collected_amount' => round(max(0, $totalOriginalAmount - $totalRemainingBalance), 2),
        ];

        $overdueNotes = (clone $baseQuery)
            ->overdue()
            ->orderBy('due_date')
            ->paginate(10, ['*'], 'overdue_page')
            ->withQueryString();

        $defaultedNotes = (clone $baseQuery)
            ->defaulted()
            ->orderByDesc('default_date')
            ->paginate(10, ['*'], 'defaulted_page')
            ->withQueryString();

        return [
            'summary' => $summary,
            'overdueNotes' => $overdueNotes,
            'defaultedNotes' => $defaultedNotes,
            'selectedSchoolYear' => $schoolYearId ? SchoolYear::find($schoolYearId) : null,
            'selectedSemester' => $semesterId ? Semester::find($semesterId) : null,
        ];
    }

    public function exportCollegeRows(int $collegeId, ?int $schoolYearId, ?int $semesterId): array
    {
        return $this->collegeScopeQuery($collegeId, $schoolYearId, $semesterId)
            ->orderByDesc('due_date')
            ->get()
            ->map(function (PromissoryNote $note) {
                $daysOverdue = $note->due_date && now()->greaterThan($note->due_date)
                    ? $note->due_date->diffInDays(now())
                    : 0;

                return [
                    'pn_id' => $note->id,
                    'student_id' => $note->student?->student_id ?? '',
                    'student_name' => $note->student?->full_name ?? '',
                    'status' => $note->status,
                    'original_amount' => (float) $note->original_amount,
                    'collected_amount' => round(max(0, (float) $note->original_amount - (float) $note->remaining_balance), 2),
                    'remaining_balance' => (float) $note->remaining_balance,
                    'due_date' => optional($note->due_date)->toDateString(),
                    'default_date' => optional($note->default_date)->toDateString(),
                    'signed_at' => optional($note->signed_at)->toDateTimeString(),
                    'issued_by' => $note->issuedBy?->full_name ?? $note->issuedBy?->name ?? '',
                    'school_year' => $note->enrollment?->schoolYear?->sy_start && $note->enrollment?->schoolYear?->sy_end
                        ? $note->enrollment->schoolYear->sy_start->format('Y') . '-' . $note->enrollment->schoolYear->sy_end->format('Y')
                        : '',
                    'semester' => $note->enrollment?->semester?->name ?? '',
                    'days_overdue' => $daysOverdue,
                ];
            })
            ->all();
    }

    public function exportCollegeQuery(int $collegeId, ?int $schoolYearId, ?int $semesterId)
    {
        return $this->collegeScopeQuery($collegeId, $schoolYearId, $semesterId)
            ->orderByDesc('due_date');
    }

    /**
     * Build a query scoped to a specific college's promissory notes.
     *
     * @param int $collegeId
     * @param int|null $schoolYearId
     * @param int|null $semesterId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function collegeScopeQuery(int $collegeId, ?int $schoolYearId, ?int $semesterId): Builder
    {
        return PromissoryNote::with([
            'student',
            'enrollment.schoolYear',
            'enrollment.semester',
            'issuedBy',
        ])->whereHas('enrollment', function ($enrollmentQuery) use ($collegeId, $schoolYearId, $semesterId) {
            $enrollmentQuery->where('college_id', $collegeId);

            if ($schoolYearId) {
                $enrollmentQuery->where('school_year_id', $schoolYearId);
            }

            if ($semesterId) {
                $enrollmentQuery->where('semester_id', $semesterId);
            }
        });
    }
}