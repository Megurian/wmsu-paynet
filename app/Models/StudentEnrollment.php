<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    const NOT_ENROLLED = 'NOT_ENROLLED';
    const FOR_PAYMENT_VALIDATION = 'FOR_PAYMENT_VALIDATION';
    const PAID = 'PAID';
    const ENROLLED = 'ENROLLED';

    const FINANCIAL_UNPAID = 'UNPAID';
    const FINANCIAL_PAID = 'PAID';
    const FINANCIAL_PARTIALLY_PAID = 'PARTIALLY_PAID';
    const FINANCIAL_DEFERRED = 'DEFERRED';
    const FINANCIAL_DEFAULT = 'DEFAULT';
    const FINANCIAL_BAD_DEBT = 'BAD_DEBT';

    protected $casts = [
        'advised_at' => 'datetime',
        'validated_at' => 'datetime',
        'assessed_at' => 'datetime',
        'cleared_for_enrollment' => 'boolean',
        'financial_status' => 'string',
        'is_void' => 'boolean',
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
        'cleared_for_enrollment',
        'financial_status',
        'is_void',
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

    public function payments()
    {
        return $this->hasMany(Payment::class, 'enrollment_id');
    }

    public function promissoryNotes()
    {
        return $this->hasMany(PromissoryNote::class, 'enrollment_id');
    }

    /**
     * Get the active (single) promissory note for this enrollment
     * Returns HasOne, which resolves to single PN or null
     */
    public function activePromissoryNote()
    {
        return $this->hasOne(PromissoryNote::class, 'enrollment_id')
                    ->where('status', PromissoryNote::STATUS_ACTIVE);
    }

    public function hasActivePromissoryNote(): bool
    {
        return $this->activePromissoryNote()->exists();
    }

    public function hasUnpaidPriorNote(): bool
    {
        return $this->promissoryNotes()
            ->whereIn('status', [
                PromissoryNote::STATUS_DEFAULT,
                PromissoryNote::STATUS_BAD_DEBT,
            ])
            ->where('remaining_balance', '>', 0)
            ->exists();
    }

    /**
     * Compute financial status based on fees and active PN
     * States: UNPAID, PAID, PARTIALLY_PAID, DEFERRED, DEFAULT, BAD_DEBT
     */
    public function computeFinancialStatus(): string
    {
        $activePromissoryNote = $this->activePromissoryNote()->first();

        if ($activePromissoryNote && (float) $activePromissoryNote->remaining_balance > 0) {
            return self::FINANCIAL_DEFERRED;
        }

        $unpaidPriorNote = $this->promissoryNotes()
            ->whereIn('status', [
                PromissoryNote::STATUS_DEFAULT,
                PromissoryNote::STATUS_BAD_DEBT,
            ])
            ->where('remaining_balance', '>', 0)
            ->orderByDesc('id')
            ->first();

        if ($unpaidPriorNote) {
            return $unpaidPriorNote->status === PromissoryNote::STATUS_DEFAULT
                ? self::FINANCIAL_DEFAULT
                : self::FINANCIAL_BAD_DEBT;
        }

        if ($this->cleared_for_enrollment || $this->status === self::PAID || $this->status === self::ENROLLED) {
            return self::FINANCIAL_PAID;
        }

        if ($this->payments()->exists()) {
            return self::FINANCIAL_PARTIALLY_PAID;
        }

        return self::FINANCIAL_UNPAID;
    }

    public function refreshFinancialStatus(): string
    {
        $financialStatus = $this->computeFinancialStatus();

        if ($this->financial_status !== $financialStatus) {
            $this->forceFill(['financial_status' => $financialStatus])->saveQuietly();
        }

        return $financialStatus;
    }

    /**
     * Scope: financially cleared students (paid or deferred)
     */
    public function scopeFinanciallyCleared($query)
    {
        return $query->whereIn('financial_status', [self::FINANCIAL_PAID, self::FINANCIAL_DEFERRED]);
    }

    /**
     * Scope: financially deferred students (active PN)
     */
    public function scopeFinanciallyDeferred($query)
    {
        return $query->where('financial_status', self::FINANCIAL_DEFERRED);
    }
}
