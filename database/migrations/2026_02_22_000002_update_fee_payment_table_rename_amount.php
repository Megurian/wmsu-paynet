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
        Schema::table('fee_payment', function (Blueprint $table) {
            if (Schema::hasColumn('fee_payment', 'amount')) {
                $table->renameColumn('amount', 'amount_paid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_payment', function (Blueprint $table) {
            if (Schema::hasColumn('fee_payment', 'amount_paid')) {
                $table->renameColumn('amount_paid', 'amount');
            }
        });
    }
};