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
        Schema::table('fees', function (Blueprint $table) {
            $table->unsignedBigInteger('created_school_year_id')->nullable()->after('organization_id');
            $table->unsignedBigInteger('created_semester_id')->nullable()->after('created_school_year_id');

            $table->foreign('created_school_year_id')->references('id')->on('school_years')->onDelete('set null');
            $table->foreign('created_semester_id')->references('id')->on('semesters')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['created_school_year_id']);
            $table->dropForeign(['created_semester_id']);
            $table->dropColumn(['created_school_year_id', 'created_semester_id']);
        });
    }
};
