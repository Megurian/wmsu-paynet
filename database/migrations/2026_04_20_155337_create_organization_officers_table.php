<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_officers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('organization_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('semester_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('role'); 

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['organization_id', 'role', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_officers');
    }
};
