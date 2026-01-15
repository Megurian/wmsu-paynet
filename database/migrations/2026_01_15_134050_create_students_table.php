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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('college_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('year_level_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('section_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('student_id'); 
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable(); 
            $table->string('contact')->nullable();
            $table->string('email')->nullable();

            $table->unique(['college_id', 'student_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
