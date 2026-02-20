<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['college_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['year_level_id']);
            $table->dropForeign(['section_id']);

            // Drop unique constraint
            $table->dropUnique('students_college_id_student_id_unique');

            // Drop columns
            $table->dropColumn(['college_id', 'course_id', 'year_level_id', 'section_id']);

            // Add new global unique constraint for student_id
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['student_id']);

            $table->unsignedBigInteger('college_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('year_level_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();

            $table->foreign('college_id')->references('id')->on('colleges')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('year_level_id')->references('id')->on('year_levels')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();

            $table->unique(['college_id', 'student_id']);
        });
    }
};
