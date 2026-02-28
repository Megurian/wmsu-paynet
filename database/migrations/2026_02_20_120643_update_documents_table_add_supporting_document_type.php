<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'Supporting Document'
        DB::statement("ALTER TABLE documents MODIFY document_type ENUM('Accreditation Certification', 'Resolution of Collection', 'Supporting Document')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, delete documents with 'Supporting Document' type to avoid data truncation
        DB::table('documents')->where('document_type', 'Supporting Document')->delete();
        
        // Then revert the enum back to original
        DB::statement("ALTER TABLE documents MODIFY document_type ENUM('Accreditation Certification', 'Resolution of Collection')");
    }
};
