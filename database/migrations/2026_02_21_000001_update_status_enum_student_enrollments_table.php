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
        // Change status enum to include NOT_ENROLLED and set as default
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->enum('status', ['NOT_ENROLLED', 'FOR_PAYMENT_VALIDATION', 'PAID', 'ENROLLED'])
                  ->default('NOT_ENROLLED')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->enum('status', ['FOR_PAYMENT_VALIDATION', 'ENROLLED', 'PAID'])
                  ->default('FOR_PAYMENT_VALIDATION')
                  ->change();
        });
    }
};
