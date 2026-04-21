<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    protected int $created = 0;
    protected int $updated = 0;
    protected int $skipped = 0;
    protected array $skippedRows = [];

    public function collection(Collection $rows)
    {
        $collegeId = Auth::user()->college_id;

        foreach ($rows as $row) {

            $row = collect($row)->mapWithKeys(function ($value, $key) {
                return [strtolower(str_replace(' ', '_', $key)) => $value];
            });

            $firstName = trim($row['first_name'] ?? '');
            $lastName  = trim($row['last_name'] ?? '');
            $email     = trim($row['email'] ?? '');

            if (!$firstName || !$lastName || !$email) {
                $this->skipped++;
                $this->skippedRows[] = $row;

                Log::warning('Skipping employee (missing fields): ' . json_encode($row));
                continue;
            }

            $existing = Employee::where('email', $email)->first();

            if ($existing && strtolower($existing->last_name) !== strtolower($lastName)) {
                $this->skipped++;

                $this->skippedRows[] = [
                    'email' => $email,
                    'file_last_name' => $lastName,
                    'db_last_name' => $existing->last_name,
                ];

                Log::warning("Skipping employee {$email}: last name mismatch");
                continue;
            }

            $wasNew = $existing === null;

            $employee = Employee::updateOrCreate(
                ['email' => $email],
                [
                    'college_id' => $collegeId,
                    'first_name' => strtoupper($firstName),
                    'middle_name' => strtoupper($row['middle_name'] ?? ''),
                    'last_name'  => strtoupper($lastName),
                    'suffix'     => $row['suffix'] ?? null,
                    'department' => $row['department'] ?? null,
                    'has_account' => $existing?->has_account ?? false,
                ]
            );

            $wasNew ? $this->created++ : $this->updated++;
        }
    }

    public function getResult(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'skipped_rows' => $this->skippedRows,
        ];
    }
}