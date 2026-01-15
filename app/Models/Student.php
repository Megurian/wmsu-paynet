<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'college_id',
        'course_id',
        'year_level_id',
        'section_id',
        'last_name',
        'first_name',
        'middle_name',
        'contact',
        'email',
        'student_id',
        'suffix',
    ];

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function yearLevel() {
        return $this->belongsTo(YearLevel::class);
    }

    public function section() {
        return $this->belongsTo(Section::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

}
