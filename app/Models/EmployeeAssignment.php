<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAssignment extends Model
{
    protected $fillable = [
        'employee_id',
        'school_year_id',
        'semester_id',
        'positions',
        'course_id',
    ];

    protected $casts = [
        'positions' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}