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
            $table->foreignId('accreditation_document_id')->nullable()->after('resolution_file')->constrained('documents')->onDelete('set null');
            $table->foreignId('resolution_document_id')->nullable()->after('accreditation_document_id')->constrained('documents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            if (Schema::hasColumn('fees', 'accreditation_document_id')) {
                $table->dropForeign(['accreditation_document_id']);
                $table->dropColumn('accreditation_document_id');
            }

            if (Schema::hasColumn('fees', 'resolution_document_id')) {
                $table->dropForeign(['resolution_document_id']);
                $table->dropColumn('resolution_document_id');
            }
        });
    }
};
