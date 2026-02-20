<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add a boolean column to allow dynamic assignment of OSA fee inheritance.
     * This replaces the hard-coded check for 'USC' organization.
     * When true, this organization will inherit fees created by OSA.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('inherits_osa_fees')->default(false)->after('status')
                ->comment('If true, this organization will inherit fees created by the Office of Student Affairs (OSA)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('inherits_osa_fees');
        });
    }
};
