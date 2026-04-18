<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('student_enrollments', 'is_void')) {
                $table->boolean('is_void')->default(false)->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('student_enrollments', 'is_void')) {
                $table->dropColumn('is_void');
            }
        });
    }
};
