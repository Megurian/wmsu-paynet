<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("UPDATE semesters SET name = CASE name WHEN '1st' THEN '1st SEMESTER' WHEN '2nd' THEN '2nd SEMESTER' WHEN 'summer' THEN 'SUMMER' ELSE name END");
        DB::statement("ALTER TABLE semesters MODIFY name ENUM('1st SEMESTER', '2nd SEMESTER', 'SUMMER') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE semesters SET name = CASE name WHEN '1st SEMESTER' THEN '1st' WHEN '2nd SEMESTER' THEN '2nd' WHEN 'SUMMER' THEN 'summer' ELSE name END");
        DB::statement("ALTER TABLE semesters MODIFY name ENUM('1st', '2nd', 'summer') NOT NULL");
    }
};
