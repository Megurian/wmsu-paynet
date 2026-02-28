<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromArray;

class StudentImportTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [], // Leave empty for spacing if needed
        ];
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Last Name',
            'First Name',
            'Middle Name',
            'Suffix',
            'Contact',
            'Email',
            'Year Level ID',
            'Section ID',
            'Religion',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']],
                'alignment' => ['horizontal' => 'center']
            ],
        ];
    }
}