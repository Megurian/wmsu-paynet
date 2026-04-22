<?php

use App\Models\ActivityLog;

if (!function_exists('log_activity')) {
    function log_activity(
        string $action,
        ?string $description = null,
        ?int $studentId = null,
        array $meta = []
    ) {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'college_id' => auth()->user()?->college_id, 
            'description' => $description,
            'student_id' => $studentId,
            'meta' => $meta,
        ]);
    }
}