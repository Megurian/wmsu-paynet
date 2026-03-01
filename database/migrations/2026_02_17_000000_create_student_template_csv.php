<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use App\Services\StudentTemplateGenerator;

class CreateStudentTemplateCsv extends Migration
{
    /**
     * Run the migrations.
     * This creates `storage/app/private/templates/student_template.csv` if missing.
     *
     * @return void
     */
    public function up()
    {
        StudentTemplateGenerator::generateIfNotExists();
    }

    /**
     * Reverse the migrations.
     * Removes the template file if it exists.
     *
     * @return void
     */
    public function down()
    {
        Storage::disk('local')->delete('templates/student_template.csv');
        Storage::disk('local')->delete('private/templates/student_template.csv');
    }
}
