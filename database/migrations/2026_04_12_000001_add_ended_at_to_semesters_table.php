<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dateTime('ended_at')->nullable()->after('is_active');
            $table->index('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropIndex(['ended_at']);
            $table->dropColumn('ended_at');
        });
    }
};