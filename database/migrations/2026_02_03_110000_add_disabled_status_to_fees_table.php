<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'disabled' to the enum list for status
        DB::statement("ALTER TABLE `fees` MODIFY `status` ENUM('pending','approved','rejected','disabled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `fees` MODIFY `status` ENUM('pending','approved','rejected') DEFAULT 'pending'");
    }
};
