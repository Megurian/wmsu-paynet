<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->date('starts_at')->nullable()->after('name');
            $table->date('will_end_at')->nullable()->after('starts_at');
            $table->index('starts_at');
            $table->index('will_end_at');
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['will_end_at']);
            $table->dropColumn(['starts_at', 'will_end_at']);
        });
    }
};