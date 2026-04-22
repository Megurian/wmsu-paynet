<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('college_id')
                ->nullable()
                ->after('user_id')
                ->constrained('colleges')
                ->cascadeOnDelete();

            $table->index('college_id');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropColumn('college_id');
        });
    }
};
