<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PromissoryNoteReportExport implements FromQuery, WithMapping, WithHeadings
{
    public function __construct(private Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function map($note): array
    {
        return [
            $note->id,
            $note->student?->student_id ?? '',
            $note->student?->full_name ?? '',
            $note->status,
            (float) $note->original_amount,
            round(max(0, (float) $note->original_amount - (float) $note->remaining_balance), 2),
            (float) $note->remaining_balance,
            optional($note->due_date)->toDateString(),
            optional($note->default_date)->toDateString(),
            optional($note->signed_at)->toDateTimeString(),
            $note->issuedBy?->full_name ?? $note->issuedBy?->name ?? '',
            $note->enrollment?->schoolYear?->sy_start && $note->enrollment?->schoolYear?->sy_end
                ? $note->enrollment->schoolYear->sy_start->format('Y') . '-' . $note->enrollment->schoolYear->sy_end->format('Y')
                : '',
            $note->enrollment?->semester?->name ?? '',
            $note->due_date && now()->greaterThan($note->due_date)
                ? $note->due_date->diffInDays(now())
                : 0,
        ];
    }

    public function headings(): array
    {
        return [
            'PN ID',
            'Student ID',
            'Student Name',
            'Status',
            'Original Amount',
            'Collected Amount',
            'Remaining Balance',
            'Due Date',
            'Default Date',
            'Signed At',
            'Issued By',
            'School Year',
            'Semester',
            'Days Overdue',
        ];
    }
}