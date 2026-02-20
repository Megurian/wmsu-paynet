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
            // Add back accreditation_document_id and resolution_document_id if they don't exist
            if (!Schema::hasColumn('fees', 'accreditation_document_id')) {
                $table->foreignId('accreditation_document_id')->nullable()->after('resolution_file')->constrained('documents')->onDelete('set null');
            }
            if (!Schema::hasColumn('fees', 'resolution_document_id')) {
                $table->foreignId('resolution_document_id')->nullable()->after('accreditation_document_id')->constrained('documents')->onDelete('set null');
            }
            // Add supporting_document_id if it doesn't exist
            if (!Schema::hasColumn('fees', 'supporting_document_id')) {
                $table->foreignId('supporting_document_id')->nullable()->after('recurrence')->constrained('documents')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            // Drop the document ID foreign keys
            if (Schema::hasColumn('fees', 'accreditation_document_id')) {
                $table->dropForeign(['accreditation_document_id']);
                $table->dropColumn('accreditation_document_id');
            }
            if (Schema::hasColumn('fees', 'resolution_document_id')) {
                $table->dropForeign(['resolution_document_id']);
                $table->dropColumn('resolution_document_id');
            }
            if (Schema::hasColumn('fees', 'supporting_document_id')) {
                $table->dropForeign(['supporting_document_id']);
                $table->dropColumn('supporting_document_id');
            }
        });
    }
};
