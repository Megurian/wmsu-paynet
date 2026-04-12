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
        'payment_type',
        'promissory_note_id',
        'amount_due',
        'cash_received',
        'change',
        'collected_by',
        'school_year_id',
        'semester_id',
        'transaction_id',
        'notes',
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

    public function promissoryNote()
    {
        return $this->belongsTo(PromissoryNote::class, 'promissory_note_id');
    }

    // ============ ACCESSORS ============

    /**
     * Check if this payment is a promissory note settlement
     */
    public function isPromissorySettlement(): bool
    {
        return $this->payment_type === 'PROMISSORY';
    }

    /**
     * Check if this is a cash payment (backward compat)
     */
    public function isCashPayment(): bool
    {
        return $this->payment_type === 'CASH';
    }

    // ============ SCOPES ============

    /**
     * Filter promissory note payments only
     */
    public function scopePromissory($query)
    {
        return $query->where('payment_type', 'PROMISSORY');
    }

    /**
     * Filter cash payments only
     */
    public function scopeCash($query)
    {
        return $query->where('payment_type', 'CASH');
    }
}