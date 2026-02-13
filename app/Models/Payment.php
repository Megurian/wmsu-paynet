<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'enrollment_id', 'amount', 'cash_received', 'change', 'collected_by', 'transaction_id'
    ];

    public function fees()
    {
        return $this->belongsToMany(Fee::class, 'fee_payment')->withPivot('amount')->withTimestamps();
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function enrollment() {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function collector() {
        return $this->belongsTo(User::class, 'collected_by');
    }
}