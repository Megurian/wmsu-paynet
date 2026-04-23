<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fee_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fee_id')->constrained()->cascadeOnDelete();

            // disable or enable
            $table->enum('type', ['disable', 'enable']);

            // workflow status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('reason');

            $table->foreignId('requested_by')->nullable()->constrained('users');
            $table->timestamp('requested_at')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();

            $table->text('review_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_requests');
    }
};