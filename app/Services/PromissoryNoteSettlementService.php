<?php

namespace App\Services;

use App\Models\PromissoryNote;
use App\Models\Payment;
use App\Models\Fee;
use App\Models\College;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Exceptions\PromissoryNoteException;
use App\Exceptions\PromissoryNoteSettlementException;
use App\Exceptions\PromissoryNoteAlreadyClosedException;
use App\Exceptions\PromissoryNotePartialAllocationException;
use App\Exceptions\PromissoryNoteLockedForUpdateException;
use App\Exceptions\PromissoryNoteNotSettleableException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PromissoryNoteSettlementService
{
    /**
     * Settle a payment against a promissory note.
     * 
     * Steps:
     * 1. Lock PN row in DB (pessimistic) to prevent concurrent settles
     * 2. Validate remaining_balance > 0
     * 3. Validate PN status allows settlement (ACTIVE, DEFAULT, BAD_DEBT)
     * 4. Allocate amount to selected fees
     * 5. Create Payment record with payment_type='PROMISSORY'
     * 6. Create pivot entries in fee_payment for each allocated fee
     * 7. Update PN remaining_balance
     * 8. If fully paid, set status='CLOSED'
     * 9. Release lock and commit transaction
     *
     * @param PromissoryNote $note
     * @param float $amount Settlement amount in dollars
     * @param array $selectedFeeIds Array of fee IDs to allocate payment to
     * @param \App\Models\User $user User collecting payment (cashier/coordinator)
     * @param bool $isOrgPayment Flag to distinguish college vs org settlement
     * 
     * @return array Settlement result: ['payment' => Payment, 'remaining_balance' => float, 'is_closed' => bool]
     * @throws PromissoryNoteSettlementException
     * @throws PromissoryNoteAlreadyClosedException
     * @throws PromissoryNotePartialAllocationException
     * @throws PromissoryNoteLockedForUpdateException
     * @throws PromissoryNoteNotSettleableException
     */
    public function settlePayment(
        PromissoryNote $note,
        $amount,
        array $selectedFeeIds,
        $user,
        $isOrgPayment = false
    )
    {
        DB::beginTransaction();

        try {
            // 1. Pessimistic lock: lock row for update (prevents concurrent modifications)
            $lockedNote = PromissoryNote::lockForUpdate()->find($note->id);

            if (!$lockedNote) {
                throw new PromissoryNoteSettlementException("Promissory note not found.");
            }

            // 2. Validate remaining balance > 0
            if ($lockedNote->remaining_balance <= 0) {
                throw new PromissoryNoteAlreadyClosedException(
                    "This promissory note has no remaining balance to settle."
                );
            }

            // 3. Validate status allows settlement
            if (!$lockedNote->canSettlePayment()) {
                throw new PromissoryNoteNotSettleableException(
                    "Promissory note status '{$lockedNote->status}' does not allow settlement. " .
                    "Must be ACTIVE, DEFAULT, or BAD_DEBT with remaining balance."
                );
            }

            // 4. Validate selected fees are legitimate fees from this PN
            if (empty($selectedFeeIds)) {
                throw new PromissoryNotePartialAllocationException("No fees selected for settlement.");
            }

            $selectedFees = Fee::whereIn('id', $selectedFeeIds)->get();
            
            if ($selectedFees->count() !== count($selectedFeeIds)) {
                throw new PromissoryNotePartialAllocationException("One or more selected fees do not exist.");
            }

            // Validate all selected fees are linked to this PN
            $pnFeeIds = $lockedNote->fees()->pluck('fees.id')->toArray();
            $invalidFees = array_diff($selectedFeeIds, $pnFeeIds);

            if (!empty($invalidFees)) {
                throw new PromissoryNotePartialAllocationException(
                    "One or more selected fees are not linked to this promissory note."
                );
            }

            // 5. Allocate amount to fees
            $settlementAmount = (float) $amount;
            $remainingToAllocate = $settlementAmount;
            $feeAllocations = []; // fee_id => amount_paid
            $totalDeferredSelected = 0;

            foreach ($selectedFees as $fee) {
                // Get deferred amount for this fee on this PN
                $deferredAmount = $lockedNote->fees()
                    ->where('fee_id', $fee->id)
                    ->value('promissory_note_fees.amount_deferred');

                if (!$deferredAmount || $deferredAmount == 0) {
                    continue;
                }

                $totalDeferredSelected += (float) $deferredAmount;

                if ($remainingToAllocate <= 0) {
                    break;
                }

                // Allocate up to the deferred amount (or remaining settlement amount, whichever is smaller)
                $amountToAllocate = min($deferredAmount, $remainingToAllocate);
                $feeAllocations[$fee->id] = $amountToAllocate;
                $remainingToAllocate -= $amountToAllocate;
            }

            if ($settlementAmount < $totalDeferredSelected) {
                throw new PromissoryNotePartialAllocationException(
                    "Selected fees require a minimum payment of {$totalDeferredSelected}."
                );
            }

            // If no fees could be allocated, throw exception
            if (empty($feeAllocations)) {
                throw new PromissoryNotePartialAllocationException(
                    "Unable to allocate settlement amount to any linked fees."
                );
            }

            // Overpayment check: if cash_received > sum of fees, no over-crediting
            $totalAllocated = array_sum($feeAllocations);
            if ($settlementAmount > $totalAllocated && !$isOrgPayment) {
                // Over-payment in college path will be returned as change
                // Do not credit excess to PN
            }

            // Only deduct from remaining balance the amount actually allocated
            $amountToDeduct = min($totalAllocated, $lockedNote->remaining_balance);

            // 6. Create Payment record
            $activeSY = SchoolYear::where('is_active', true)->first();
            $activeSem = Semester::where('is_active', true)->first();

            $collegeId = $lockedNote->enrollment->college_id ?? Auth::user()->college_id;
            $organizationId = $isOrgPayment ? (Auth::user()->organization_id ?? null) : null;

            $college = College::find($collegeId);
            $collegeCode = ($college?->college_code ?? 'UNK') . ($isOrgPayment ? '-ORG' : '-TRES');
            $dateStr = now()->format('Ymd');

            if ($organizationId) {
                $countToday = Payment::where('organization_id', $organizationId)
                    ->whereDate('created_at', now())
                    ->count();
            } else {
                $countToday = Payment::whereNull('organization_id')
                    ->whereHas('enrollment', function ($query) use ($collegeId) {
                        $query->where('college_id', $collegeId);
                    })
                    ->whereDate('created_at', now())
                    ->count();
            }

            $sequenceNum = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
            $randomSuffix = strtoupper(str_pad(dechex(random_int(0, 4095)), 3, '0', STR_PAD_LEFT));
            $transactionId = "{$collegeCode}-{$dateStr}-{$sequenceNum}-{$randomSuffix}";

            $payment = Payment::create([
                'student_id' => $lockedNote->student_id,
                'enrollment_id' => $lockedNote->enrollment_id,
                'organization_id' => $organizationId,
                'school_year_id' => $activeSY->id ?? null,
                'semester_id' => $activeSem->id ?? null,
                'payment_type' => 'PROMISSORY',
                'promissory_note_id' => $lockedNote->id,
                'amount_due' => $totalAllocated,
                'cash_received' => $settlementAmount,
                'change' => $settlementAmount - $totalAllocated,
                'collected_by' => $user->id,
                'transaction_id' => $transactionId,
                'notes' => "PN Settlement: {$lockedNote->id}",
            ]);

            // 7. Create fee_payment pivot entries
            foreach ($feeAllocations as $feeId => $amountPaid) {
                $payment->fees()->attach($feeId, ['amount_paid' => $amountPaid]);
            }

            // 8. Update PN remaining_balance
            $newBalance = max(0, $lockedNote->remaining_balance - $amountToDeduct);
            $lockedNote->remaining_balance = $newBalance;

            // 9. Check if fully paid → close PN
            $isClosed = false;
            if ($newBalance <= 0) {
                $lockedNote->status = PromissoryNote::STATUS_CLOSED;
                $isClosed = true;
            }

            $lockedNote->save();

            DB::commit();

            Log::info('Promissory note settlement recorded', [
                'promissory_note_id' => $lockedNote->id,
                'student_id' => $lockedNote->student_id,
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'collected_by' => $user->id,
                'amount_received' => $settlementAmount,
                'allocated_amount' => $totalAllocated,
                'remaining_balance' => $newBalance,
                'is_closed' => $isClosed,
                'is_org_payment' => $isOrgPayment,
            ]);

            return [
                'payment' => $payment,
                'remaining_balance' => $newBalance,
                'is_closed' => $isClosed,
                'allocated_fees' => array_keys($feeAllocations),
                'transaction_id' => $transactionId,
            ];

        } catch (Throwable $e) {
            DB::rollBack();
            
            // Re-throw if it's already a PromissoryNoteException
            if ($e instanceof PromissoryNoteException) {
                throw $e;
            }

            // Handle database lock timeout
            if (str_contains($e->getMessage(), 'lock')) {
                throw new PromissoryNoteLockedForUpdateException(
                    "Failed to process payment due to concurrent access. Please try again.",
                    409,
                    $e
                );
            }

            // Generic settlement error
            throw new PromissoryNoteSettlementException(
                "Failed to process promissory note settlement: " . $e->getMessage(),
                500,
                $e
            );
        }
    }

    /**
     * Validate that a PN can be settled (shared pre-flight check before settlement flow)
     * Does not lock row; purely informational for UI flow.
     *
     * @param PromissoryNote $note
     * @return array ['can_settle' => bool, 'reason' => string|null]
     */
    public function validateSettleability(PromissoryNote $note): array
    {
        if ($note->remaining_balance <= 0) {
            return ['can_settle' => false, 'reason' => 'No remaining balance.'];
        }

        if (!$note->canSettlePayment()) {
            return [
                'can_settle' => false,
                'reason' => "Status '{$note->status}' does not allow settlement."
            ];
        }

        if ($note->fees()->count() === 0) {
            return ['can_settle' => false, 'reason' => 'No fees linked to this note.'];
        }

        return ['can_settle' => true, 'reason' => null];
    }

    /**
     * Get settlement summary for a PN (for UI display)
     *
     * @param PromissoryNote $note
     * @return array Settlement context
     */
    public function getSettlementSummary(PromissoryNote $note): array
    {
        return [
            'promissory_note_id' => $note->id,
            'student_id' => $note->student_id,
            'original_amount' => $note->original_amount,
            'remaining_balance' => $note->remaining_balance,
            'status' => $note->status,
            'due_date' => $note->due_date->toDateString(),
            'fees' => $note->fees()->get()->map(function ($fee) {
                return [
                    'fee_id' => $fee->id,
                    'name' => $fee->fee_name,
                    'amount_deferred' => $fee->pivot->amount_deferred,
                ];
            })->toArray(),
            'can_settle' => $note->canSettlePayment(),
        ];
    }
}
