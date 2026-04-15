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
        Schema::create('promissory_note_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promissory_note_id')->constrained('promissory_notes')->cascadeOnDelete();
            $table->foreignId('fee_id')->constrained('fees')->cascadeOnDelete();
            $table->decimal('amount_deferred', 12, 2);
            $table->timestamps();
            
            // Unique pivot key
            $table->unique(['promissory_note_id', 'fee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promissory_note_fees');
    }
};
