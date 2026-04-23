<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {

            $table->unsignedBigInteger('disable_approved_by')->nullable();

            $table->timestamp('disable_approved_at')->nullable();

            $table->foreign('disable_approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {

            $table->dropForeign(['disable_approved_by']);
            $table->dropColumn([
                'disable_approved_by',
                'disable_approved_at',
            ]);
        });
    }
};