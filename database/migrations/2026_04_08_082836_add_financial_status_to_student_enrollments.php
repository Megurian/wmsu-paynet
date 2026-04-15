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
            $table->enum('financial_status', [
                'PAID',
                'PARTIALLY_PAID',
                'DEFERRED',
                'DEFAULT',
                'BAD_DEBT',
            ])->default('PAID')->after('status');

            $table->index('financial_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropIndex(['financial_status']);
            $table->dropColumn('financial_status');
        });
    }
};
