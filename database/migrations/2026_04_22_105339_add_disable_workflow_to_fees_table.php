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
            $table->enum('disable_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('disable_reason')->nullable();
            $table->timestamp('disable_requested_at')->nullable();
            $table->unsignedBigInteger('disable_requested_by')->nullable();

            $table->foreign('disable_requested_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['disable_requested_by']);
            $table->dropColumn([
                'disable_status',
                'disable_reason',
                'disable_requested_at',
                'disable_requested_by'
            ]);
        });
    }
};
