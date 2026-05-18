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
        $tableName = 'organization_officers';

        // Always try to drop the old foreign key constraint first
        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign('organization_officers_user_id_foreign');
            });
        } catch (\Throwable $e) {
            // Constraint doesn't exist, continue
        }

        // Add user_id column if it doesn't exist
        if (!Schema::hasColumn($tableName, 'user_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('student_id');
            });
        }

        // Add the foreign key constraint
        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // Constraint already exists, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_officers', function (Blueprint $table) {
            try {
                $table->dropForeign('organization_officers_user_id_foreign');
            } catch (\Throwable $e) {
                // Constraint doesn't exist
            }
            
            if (Schema::hasColumn('organization_officers', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
