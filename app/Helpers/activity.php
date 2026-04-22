<?php

use App\Models\ActivityLog;

if (!function_exists('log_activity')) {
    function log_activity(
        string $action,
        ?string $description = null,
        ?int $studentId = null,
        ?int $employeeId = null,
        ?int $officerId = null,
        array $meta = []
    ) {
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'college_id'  => auth()->user()?->college_id,

            'action'      => $action,
            'description' => $description,

            'student_id'  => $studentId,
            'employee_id' => $employeeId,
            'officer_id'  => $officerId,

            'meta'        => $meta,
        ]);
    }
}