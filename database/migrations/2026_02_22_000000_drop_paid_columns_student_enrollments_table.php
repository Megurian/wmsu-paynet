<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops the is_paid and paid_at columns which were redundant.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('student_enrollments', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
            if (Schema::hasColumn('student_enrollments', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Re-adds the columns in case of rollback.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('status');
            $table->timestamp('paid_at')->nullable()->after('is_paid');
        });
    }
};
