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
            if (Schema::hasColumn('payments', 'amount')) {
                $table->renameColumn('amount', 'amount_due');
            }
            if (!Schema::hasColumn('payments', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'school_year_id')) {
                $table->foreignId('school_year_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'semester_id')) {
                $table->foreignId('semester_id')->nullable()->constrained()->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'amount_due')) {
                $table->renameColumn('amount_due', 'amount');
            }

            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');

            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');

            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });
    }
};