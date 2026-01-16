<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_years', function (Blueprint $table) {
            $table->unique(['sy_start', 'sy_end'], 'school_year_unique_range');
        });
    }

    public function down(): void
    {
        Schema::table('school_years', function (Blueprint $table) {
            $table->dropUnique('school_year_unique_range');
        });
    }
};
