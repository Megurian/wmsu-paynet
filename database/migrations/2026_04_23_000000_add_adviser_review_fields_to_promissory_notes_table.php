<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promissory_notes', function (Blueprint $table) {
            $table->dateTime('adviser_reviewed_at')->nullable()->after('document_path');
            $table->foreignId('adviser_reviewed_by')
                ->nullable()
                ->after('adviser_reviewed_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('adviser_review_notes')->nullable()->after('adviser_reviewed_by');
        });

        DB::statement("ALTER TABLE promissory_notes MODIFY status ENUM('PENDING_SIGNATURE','PENDING_ADVISER_VERIFICATION','PENDING_VERIFICATION','ACTIVE','VOIDED','CLOSED','DEFAULT','BAD_DEBT') NOT NULL DEFAULT 'PENDING_SIGNATURE'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE promissory_notes MODIFY status ENUM('PENDING_SIGNATURE','PENDING_VERIFICATION','ACTIVE','VOIDED','CLOSED','DEFAULT','BAD_DEBT') NOT NULL DEFAULT 'PENDING_SIGNATURE'");

        Schema::table('promissory_notes', function (Blueprint $table) {
            $table->dropForeign(['adviser_reviewed_by']);
            $table->dropColumn(['adviser_reviewed_at', 'adviser_reviewed_by', 'adviser_review_notes']);
        });
    }
};
