<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'contact',
        'email',
        'religion'
    ];

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Get the student's current enrollment record.
     */
    public function currentEnrollment()
    {
        // Try to get the enrollment for the active school year/semester
        return $this->hasOne(StudentEnrollment::class)
            ->whereHas('schoolYear', function ($query) {
                $query->where('is_active', true);
            })
            ->whereHas('semester', function ($query) {
                $query->where('is_active', true);
            });
    }

    /**
     * Get the most recent enrollment record regardless of active status.
     */
    public function latestEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)->latestOfMany();
    }
}
