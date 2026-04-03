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
        Schema::create('remittances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('to_organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('fee_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('amount', 10, 2);

            $table->foreignId('school_year_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', ['pending', 'confirmed'])->default('confirmed');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remittances');
    }
};
