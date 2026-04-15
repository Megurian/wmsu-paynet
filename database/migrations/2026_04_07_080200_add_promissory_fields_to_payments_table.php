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
        Schema::table('payments', function (Blueprint $table) {
            // Add payment_type: CASH or PROMISSORY, default CASH for backward compatibility
            $table->enum('payment_type', ['CASH', 'PROMISSORY'])->default('CASH')->after('transaction_id');
            
            // Add foreign key to promissory_notes, nullable for CASH payments
            $table->foreignId('promissory_note_id')
                ->nullable()
                ->constrained('promissory_notes')
                ->cascadeOnDelete()
                ->after('payment_type');
            
            // Index for quick settlement lookups
            $table->index(['promissory_note_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['promissory_note_id']);
            $table->dropIndex(['promissory_note_id', 'created_at']);
            $table->dropColumn(['payment_type', 'promissory_note_id']);
        });
    }
};
