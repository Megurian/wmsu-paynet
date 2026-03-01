<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'organization_id',
        'amount_due',
        'cash_received',
        'change',
        'collected_by',
        'school_year_id',
        'semester_id',
        'transaction_id'
    ];

    public function fees()
    {
        // pivot stores how much was actually paid for each fee
        return $this->belongsToMany(Fee::class, 'fee_payment')
                    ->withPivot('amount_paid')
                    ->withTimestamps();
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(\App\Models\SchoolYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class);
    }

    public function enrollment() {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function collector() {
        return $this->belongsTo(User::class, 'collected_by');
    }
}