<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->after('is_active');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropIndex(['started_at']);
            $table->dropColumn(['started_at']);
        });
    }
};
