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
        Schema::table('fees', function (Blueprint $table) {

            $table->foreignId('organization_id')
            ->nullable()
            ->change();

            // Organization fees OR College fees
            $table->enum('fee_scope', ['organization', 'college'])
                  ->after('id');

            // College owner (for college-local fees)
            $table->foreignId('college_id')
                  ->nullable()
                  ->after('organization_id')
                  ->constrained()
                  ->nullOnDelete();

            // Who must approve this fee
            $table->enum('approval_level', ['osa', 'dean'])
                  ->after('fee_scope');

            // Approval tracking
            $table->foreignId('approved_by')
                  ->nullable()
                  ->after('status')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('approved_at')
                  ->nullable()
                  ->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn([
                'fee_scope',
                'college_id',
                'approval_level',
                'approved_by',
                'approved_at',
            ]);
        });
    }
};
