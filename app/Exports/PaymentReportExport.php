<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentReportExport implements FromCollection, WithMapping, WithHeadings
{
    public function __construct(private iterable $studentsWithPayments)
    {
    }

    public function collection(): Collection
    {
        return collect($this->studentsWithPayments);
    }

    public function map($row): array
    {
        $student = data_get($row, 'student');

        return [
            data_get($student, 'student_id', ''),
            trim((string) data_get($student, 'last_name', '') . ', ' . data_get($student, 'first_name', '')),
            (int) data_get($row, 'payments_count', collect(data_get($row, 'payments', []))->count()),
            number_format((float) data_get($row, 'total_paid', 0), 2, '.', ''),
            number_format((float) data_get($row, 'total_pending', 0), 2, '.', ''),
        ];
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Student Name',
            'Payment Count',
            'Total Paid',
            'Total Pending',
        ];
    }
}