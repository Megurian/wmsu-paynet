<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    const NOT_ENROLLED = 'NOT_ENROLLED';
    const FOR_PAYMENT_VALIDATION = 'FOR_PAYMENT_VALIDATION';
    const PAID = 'PAID';
    const ENROLLED = 'ENROLLED';

    protected $casts = [
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'advised_at' => 'datetime',
        'validated_at' => 'datetime',
        'assessed_at' => 'datetime',
    ];

    protected $fillable = [
        'student_id',
        'college_id',
        'course_id',
        'year_level_id',
        'section_id',
        'school_year_id',
        'semester_id',
        'status',
        'adviser_id',
        'advised_at',
        'validated_by',
        'validated_at',
        'assessed_by',
        'assessed_at',
        'is_paid',
        'paid_at', 
        'cleared_for_enrollment',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function yearLevel() {
        return $this->belongsTo(YearLevel::class, 'year_level_id');
    }

    public function section() {
        return $this->belongsTo(Section::class);
    }

    public function schoolYear() {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function semester() {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
    public function markAsPaid()
{
    $this->update([
        'is_paid' => true,
        'paid_at' => now(),
    ]);
}


}
