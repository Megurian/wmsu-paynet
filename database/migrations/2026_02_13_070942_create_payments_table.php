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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained('student_enrollments')->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('amount_due', 10, 2);
            $table->decimal('cash_received', 10, 2);
            $table->decimal('change', 10, 2);
            $table->foreignId('collected_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('school_year_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
