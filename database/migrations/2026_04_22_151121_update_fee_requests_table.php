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
        Schema::table('fee_requests', function (Blueprint $table) {
            $table->timestamp('disable_approved_at')->nullable();
             $table->timestamp('enable_approved_at')->nullable();
             $table->timestamp('enabled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_request', function (Blueprint $table) {
            //
        });
    }
};
