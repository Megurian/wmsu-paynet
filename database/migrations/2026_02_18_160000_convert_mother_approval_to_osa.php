<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert any existing fees stuck at the 'mother' approval level to 'osa'
     * so they continue through the updated workflow (dean -> OSA).
     */
    public function up(): void
    {
        DB::table('fees')
            ->where('approval_level', 'mother')
            ->update(['approval_level' => 'osa']);
    }

    /**
     * Reverse the migrations.
     * This operation is intentionally irreversible because we cannot reliably
     * determine which 'osa' rows were previously 'mother'.
     */
    public function down(): void
    {
        // no-op
    }
};
