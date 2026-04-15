<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promissory_notes', function (Blueprint $table) {
            $table->foreignId('open_student_id')
                ->nullable()
                ->after('student_id')
                ->constrained('students')
                ->nullOnDelete();
        });

        DB::table('promissory_notes')
            ->whereIn('status', [
                'PENDING_SIGNATURE',
                'PENDING_VERIFICATION',
                'ACTIVE',
            ])
            ->update(['open_student_id' => DB::raw('student_id')]);

        Schema::table('promissory_notes', function (Blueprint $table) {
            $table->unique('open_student_id', 'unique_student_open_pn');
            $table->dropUnique('unique_student_active_pn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promissory_notes', function (Blueprint $table) {
            $table->dropUnique('unique_student_open_pn');
            $table->dropConstrainedForeignId('open_student_id');
            $table->unique('student_id', 'unique_student_active_pn');
        });
    }
};