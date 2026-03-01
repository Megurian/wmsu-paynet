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
            // Drop legacy file columns if they exist
            if (Schema::hasColumn('fees', 'accreditation_file')) {
                $table->dropColumn('accreditation_file');
            }
            if (Schema::hasColumn('fees', 'resolution_file')) {
                $table->dropColumn('resolution_file');
            }
        });

        Schema::table('fees', function (Blueprint $table) {
            // Add supporting document foreign key after recurrence
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
            // Drop supporting document foreign key
            $table->dropForeign(['supporting_document_id']);
            $table->dropColumn(['supporting_document_id']);
            
            // Re-add legacy columns
            $table->string('accreditation_file')->nullable()->after('requirement_level');
            $table->string('resolution_file')->nullable()->after('accreditation_file');
        });
    }
};
