<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            // Add new columns first
            $table->timestamp('advised_at')->nullable()->after('adviser_id');
            $table->unsignedBigInteger('assessed_by')->nullable()->after('validated_at');
            $table->foreign('assessed_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('assessed_at')->nullable()->after('assessed_by');
            
            // Change order by dropping and re-adding cleared_for_enrollment after assessed_at
            // We'll do this via raw SQL since Laravel's schema builder doesn't have a great column order change
        });

        // Use raw SQL to restructure columns if needed (database specific)
        // For MySQL, we can use MODIFY and AFTER
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            Schema::table('student_enrollments', function (Blueprint $table) {
                // Drop the old cleared_for_enrollment constraint if exists, then move it
                try {
                    DB::statement('ALTER TABLE student_enrollments MODIFY cleared_for_enrollment BOOLEAN DEFAULT false AFTER assessed_at');
                } catch (\Exception $e) {
                    // Column might already be in the right place or doesn't exist
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['assessed_by']);
            $table->dropColumn(['advised_at', 'assessed_by', 'assessed_at']);
        });
    }
};
