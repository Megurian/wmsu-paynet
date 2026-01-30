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
            $table->enum('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED', 'PAID'])->default('FOR_PAYMENT_VALIDATION')->after('semester_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn(['status', 'validated_at']);
        });
    }
};
