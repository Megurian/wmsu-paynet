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
        Schema::table('organizations', function (Blueprint $table) {
            // Make status nullable
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->nullable()
                  ->default(null)
                  ->change();
        });

        // Optional: set status = null for existing mother/university orgs
        \App\Models\Organization::whereIn('role', ['university_org'])
            ->orWhereNotNull('mother_organization_id')
            ->update(['status' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('organizations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->nullable(false)
                  ->change();
        });
    }
};
