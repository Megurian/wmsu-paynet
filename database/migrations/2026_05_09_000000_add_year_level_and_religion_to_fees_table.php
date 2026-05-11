<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->foreignId('year_level_id')->nullable()->constrained('year_levels')->nullOnDelete();
            $table->foreignId('religion_id')->nullable()->constrained('religions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['year_level_id']);
            $table->dropForeign(['religion_id']);
            $table->dropColumn(['year_level_id', 'religion_id']);
        });
    }
};
