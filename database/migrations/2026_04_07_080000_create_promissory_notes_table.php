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
        Schema::create('promissory_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('student_enrollments')->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            
            // Status: PENDING_SIGNATURE, PENDING_VERIFICATION, ACTIVE, VOIDED, CLOSED, DEFAULT, BAD_DEBT
            $table->enum('status', [
                'PENDING_SIGNATURE',
                'PENDING_VERIFICATION',
                'ACTIVE',
                'VOIDED',
                'CLOSED',
                'DEFAULT',
                'BAD_DEBT'
            ])->default('PENDING_SIGNATURE');
            
            $table->decimal('original_amount', 12, 2);
            $table->decimal('remaining_balance', 12, 2);
            $table->date('due_date');
            $table->dateTime('signature_deadline');
            
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('students')->cascadeOnDelete();
            $table->string('document_path')->nullable();
            
            $table->dateTime('default_date')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index(['student_id', 'status']);
            $table->index(['enrollment_id']);
            $table->index(['due_date']);
            
            // Filtered unique index: only one active/pending PN per student at any time
            // This prevents race condition of simultaneous issuance
            $table->unique(['student_id'], 'unique_student_active_pn')
                ->where('status', 'PENDING_SIGNATURE')
                ->orWhere('status', 'ACTIVE')
                ->orWhere('status', 'PENDING_VERIFICATION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promissory_notes');
    }
};
