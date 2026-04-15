<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE student_enrollments MODIFY financial_status ENUM('UNPAID', 'PAID', 'PARTIALLY_PAID', 'DEFERRED', 'DEFAULT', 'BAD_DEBT') NOT NULL DEFAULT 'UNPAID' AFTER status");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE student_enrollments MODIFY financial_status ENUM('PAID', 'PARTIALLY_PAID', 'DEFERRED', 'DEFAULT', 'BAD_DEBT') NOT NULL DEFAULT 'PAID' AFTER status");
    }
};