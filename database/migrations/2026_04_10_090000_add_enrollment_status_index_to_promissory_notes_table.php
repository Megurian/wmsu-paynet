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
        Schema::table('promissory_notes', function (Blueprint $table) {
            $table->index(['enrollment_id', 'status'], 'promissory_notes_enrollment_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promissory_notes', function (Blueprint $table) {
            $table->dropIndex('promissory_notes_enrollment_status_index');
        });
    }
};