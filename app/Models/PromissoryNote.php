<?php

namespace App\Models;

use App\Services\PromissoryNoteIssuanceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PromissoryNote extends Model
{
    use HasFactory;

    public const OPEN_STATUSES = [
        self::STATUS_PENDING_SIGNATURE,
        self::STATUS_PENDING_VERIFICATION,
        self::STATUS_ACTIVE,
    ];

    // Status constants
    const STATUS_PENDING_SIGNATURE = 'PENDING_SIGNATURE';
    const STATUS_PENDING_VERIFICATION = 'PENDING_VERIFICATION';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_VOIDED = 'VOIDED';
    const STATUS_CLOSED = 'CLOSED';
    const STATUS_DEFAULT = 'DEFAULT';
    const STATUS_BAD_DEBT = 'BAD_DEBT';

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'issued_by',
        'status',
        'original_amount',
        'remaining_balance',
        'due_date',
        'signature_deadline',
        'signed_at',
        'signed_by',
        'document_path',
        'default_date',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'signature_deadline' => 'datetime',
        'signed_at' => 'datetime',
        'default_date' => 'datetime',
        'original_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (PromissoryNote $promissoryNote) {
            $promissoryNote->open_student_id = $promissoryNote->occupiesOpenStudentSlot()
                ? $promissoryNote->student_id
                : null;
        });
    }

    // ============ RELATIONSHIPS ============

    /**
     * The student who has the promissory note
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * The enrollment associated with this PN
     */
    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    /**
     * The user (coordinator/cashier) who issued this PN
     */
    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * The student who signed the PN (typically the student themselves)
     */
    public function signatary()
    {
        return $this->belongsTo(Student::class, 'signed_by');
    }

    /**
     * The fees deferred by this PN (many-to-many via promissory_note_fees)
     */
    public function fees()
    {
        return $this->belongsToMany(Fee::class, 'promissory_note_fees')
                    ->withPivot('amount_deferred')
                    ->withTimestamps();
    }

    /**
     * The payments made against this PN (one PN to many payments)
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'promissory_note_id');
    }

    // ============ STATE ACCESSORS ============

    /**
     * Check if PN is in ACTIVE status
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if PN is awaiting student signature
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_SIGNATURE;
    }

    /**
     * Check if PN is awaiting coordinator verification/approval
     */
    public function isPendingVerification(): bool
    {
        return $this->status === self::STATUS_PENDING_VERIFICATION;
    }

    /**
     * Check if PN was voided (expired without signature or rejected)
     */
    public function isVoided(): bool
    {
        return $this->status === self::STATUS_VOIDED;
    }

    /**
     * Check if PN is fully paid and closed
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Check if PN is in DEFAULT status (past due, not paid)
     */
    public function isDefaulted(): bool
    {
        return $this->status === self::STATUS_DEFAULT;
    }

    /**
     * Check if PN is in BAD_DEBT status (severely overdue, institutional write-off)
     */
    public function isBadDebt(): bool
    {
        return $this->status === self::STATUS_BAD_DEBT;
    }

    /**
     * Check whether this PN should occupy the one-open-note database slot.
     */
    public function occupiesOpenStudentSlot(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    /**
     * Check if PN can accept settlement payments
     * (ACTIVE, DEFAULT, or BAD_DEBT with balance remaining)
     */
    public function canSettlePayment(): bool
    {
        return ($this->isActive() || $this->isDefaulted() || $this->isBadDebt()) 
               && $this->remaining_balance > 0;
    }

    /**
     * Check if signature deadline has passed (for voiding)
     */
    public function isSignatureOverdue(): bool
    {
        return $this->signature_deadline < now() && $this->isPending();
    }

    // ============ SCOPES ============

    /**
     * Get active (non-voided, non-closed) PNs
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Get pending signature PNs
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_SIGNATURE);
    }

    /**
     * Get pending verification PNs awaiting coordinator approval
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', self::STATUS_PENDING_VERIFICATION);
    }

    /**
     * Get overdue PNs (past due date and in collectible status)
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
                     ->whereIn('status', [
                         self::STATUS_ACTIVE,
                         self::STATUS_DEFAULT,
                         self::STATUS_BAD_DEBT,
                     ]);
    }

    /**
     * Filter PNs by student
     */
    public function scopeForStudent($query, $student_id)
    {
        return $query->where('student_id', $student_id);
    }

    /**
     * Get PNs for a specific enrollment
     */
    public function scopeForEnrollment($query, $enrollment_id)
    {
        return $query->where('enrollment_id', $enrollment_id);
    }

    /**
     * Get voided PNs
     */
    public function scopeVoided($query)
    {
        return $query->where('status', self::STATUS_VOIDED);
    }

    /**
     * Get closed (fully paid) PNs
     */
    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Get defaulted PNs
     */
    public function scopeDefaulted($query)
    {
        return $query->where('status', self::STATUS_DEFAULT);
    }

    /**
     * Get bad debt PNs
     */
    public function scopeBadDebt($query)
    {
        return $query->where('status', self::STATUS_BAD_DEBT);
    }

    // ============ METHODS ============

    /**
     * Void the PN and detach all linked fees
     * Called when signature deadline expires or coordinator rejects
     */
    public function void(): void
    {
        DB::transaction(function () {
            $this->status = self::STATUS_VOIDED;
            $this->save();

            // Detach all fees to prevent "orphaned fee" trap
            // Fees revert to UNPAID state
            $this->fees()->detach();
        });
    }

    /**
     * Transition PN to PENDING_VERIFICATION status after student uploads signature
     * (Not ACTIVE yet; coordinator must approve)
     */
    public function pendingVerification(): void
    {
        $this->status = self::STATUS_PENDING_VERIFICATION;
        $this->save();
    }

    /**
     * Activate the PN after coordinator approves signature
     * (Two-gate security: file validation + coordinator review)
     */
    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * Submit a signed note for coordinator review.
     * Delegates the transition to the issuance service.
     */
    public function sign(Student $student, string $signatureFilePath): self
    {
        return app(PromissoryNoteIssuanceService::class)->validateSignature(
            $this,
            $signatureFilePath,
            $student
        );
    }

    /**
     * Mark as DEFAULT (past due, not paid)
     * Called by delinquency scheduler at end of semester
     */
    public function markDefault(): void
    {
        $this->status = self::STATUS_DEFAULT;
        $this->default_date = now();
        $this->save();
    }

    /**
     * Mark as BAD_DEBT (severely overdue, institutional write-off)
     * Called by delinquency scheduler at end of academic year
     * Note: Remains settleable indefinitely; only blocks next-semester enrollment
     */
    public function markBadDebt(): void
    {
        $this->status = self::STATUS_BAD_DEBT;
        $this->save();
    }

    /**
     * Close the PN (fully paid)
     * Called after final payment settles remaining balance
     */
    public function close(): void
    {
        $this->status = self::STATUS_CLOSED;
        $this->remaining_balance = 0;
        $this->save();
    }

    /**
     * Allocate payment amount to selected fees
     * Returns: [fees_settled => [...], remaining_amount => $float]
     * 
     * @param float $payment_amount Amount received from student/cashier
     * @param array $fee_ids Array of fee IDs student selected to pay
     * @return array Settlement details
     */
    public function settlePayment($payment_amount, $fee_ids = []): array
    {
        if ($this->remaining_balance <= 0) {
            return [
                'settled' => false,
                'reason' => 'PN already fully paid or closed',
                'amount_allocated' => 0,
                'remaining_balance' => $this->remaining_balance,
            ];
        }

        // If no fees specified, allocate against all deferred fees (proportional)
        if (empty($fee_ids)) {
            $fee_ids = $this->fees()->pluck('id')->toArray();
        }

        $fees_settled = [];
        $amount_remaining = $payment_amount;

        foreach ($fee_ids as $fee_id) {
            if ($amount_remaining <= 0) {
                break;
            }

            $pivot = $this->fees()
                ->where('fee_id', $fee_id)
                ->first();

            if (!$pivot) {
                continue;
            }

            // Amount to allocate to this fee (up to deferred amount)
            $deferred_amount = $pivot->pivot->amount_deferred;
            $allocate = min($amount_remaining, $deferred_amount);

            $fees_settled[] = [
                'fee_id' => $fee_id,
                'amount_allocated' => $allocate,
            ];

            $amount_remaining -= $allocate;
        }

        // Update PN balance
        $this->remaining_balance -= ($payment_amount - $amount_remaining);
        $this->save();

        return [
            'settled' => true,
            'fees_settled' => $fees_settled,
            'amount_allocated' => $payment_amount - $amount_remaining,
            'remaining_balance' => $this->remaining_balance,
            'is_closed' => $this->remaining_balance <= 0,
        ];
    }

    /**
     * Check if this PN should transition to DEFAULT status (overdue)
     * Logic: if past due date AND still in ACTIVE status, transition to DEFAULT
     * 
     * @param \DateTime|null $currentDate Current date for evaluation (defaults to now())
     * @return bool True if transitioned to DEFAULT, False if no change
     */
    public function checkForDefault($currentDate = null): bool
    {
        $currentDate = $currentDate ?? now();

        if ($this->status === self::STATUS_ACTIVE && $currentDate->toDate() > $this->due_date) {
            $this->status = self::STATUS_DEFAULT;
            $this->default_date = $currentDate->toDate();
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Retrieve a PN from DEFAULT status back to ACTIVE
     * Called when partial payment is received on a previously defaulted note
     */
    public function retrieveFromDefault(): void
    {
        if ($this->isDefaulted()) {
            $this->status = self::STATUS_ACTIVE;
            $this->default_date = null;
            $this->save();
        }
    }
}
