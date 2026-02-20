<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Restructure student_enrollments table to have proper column order:
     * status -> adviser_id -> advised_at -> validated_by -> validated_at -> assessed_by -> assessed_at -> cleared_for_enrollment
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // For MySQL, we can use MODIFY with AFTER to reorder columns
            DB::statement('ALTER TABLE student_enrollments MODIFY adviser_id BIGINT UNSIGNED NULL AFTER status');
            DB::statement('ALTER TABLE student_enrollments MODIFY is_paid BOOLEAN DEFAULT 0 AFTER advised_at');
            DB::statement('ALTER TABLE student_enrollments MODIFY paid_at TIMESTAMP NULL AFTER is_paid');
            DB::statement('ALTER TABLE student_enrollments MODIFY validated_by BIGINT UNSIGNED NULL AFTER paid_at');
            DB::statement('ALTER TABLE student_enrollments MODIFY validated_at TIMESTAMP NULL AFTER validated_by');
            DB::statement('ALTER TABLE student_enrollments MODIFY cleared_for_enrollment BOOLEAN DEFAULT 0 AFTER assessed_at');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversing column order is complex, so we'll skip it for now
        // The migrations can be rolled back manually if needed
    }
};
