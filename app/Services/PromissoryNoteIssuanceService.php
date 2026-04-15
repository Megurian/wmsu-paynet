<?php

namespace App\Services;

use App\Models\PromissoryNote;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PromissoryNoteIssuanceService
{
    public function issueNote(
        StudentEnrollment $enrollment,
        iterable $unpaidMandatoryFees,
        User $issuer,
        ?Carbon $dueDate = null,
        ?string $notes = null
    ): PromissoryNote {
        return DB::transaction(function () use ($enrollment, $unpaidMandatoryFees, $issuer, $dueDate, $notes) {
            $fees = collect($unpaidMandatoryFees)->values();

            $originalAmount = $fees->sum(function ($fee) {
                return (float) (data_get($fee, 'amount_deferred') ?? data_get($fee, 'amount') ?? 0);
            });

            if ($originalAmount <= 0) {
                throw new \RuntimeException('Cannot issue a promissory note without deferred fees.');
            }

            try {
                $promissoryNote = PromissoryNote::create([
                    'student_id' => $enrollment->student_id,
                    'enrollment_id' => $enrollment->id,
                    'issued_by' => $issuer->id,
                    'status' => PromissoryNote::STATUS_PENDING_SIGNATURE,
                    'original_amount' => $originalAmount,
                    'remaining_balance' => $originalAmount,
                    'due_date' => ($dueDate ?? now()->addDays(30))->toDateString(),
                    'signature_deadline' => now()->addDays(3),
                    'notes' => $notes ? trim($notes) : null,
                ]);
            } catch (QueryException $exception) {
                if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                    throw new \RuntimeException('Student already has an active or pending promissory note.');
                }

                throw $exception;
            }

            foreach ($fees as $fee) {
                $feeId = data_get($fee, 'id');
                if (! $feeId) {
                    continue;
                }

                $amountDeferred = (float) (data_get($fee, 'amount_deferred') ?? data_get($fee, 'amount') ?? 0);

                $promissoryNote->fees()->attach($feeId, [
                    'amount_deferred' => $amountDeferred,
                ]);
            }

            return $promissoryNote->fresh(['student', 'enrollment', 'fees']);
        });
    }

    public function validateSignature(PromissoryNote $note, string $signatureFilePath, Student $student): PromissoryNote
    {
        return DB::transaction(function () use ($note, $signatureFilePath, $student) {
            $lockedNote = PromissoryNote::whereKey($note->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedNote->isVoided() || $lockedNote->isClosed()) {
                throw new \RuntimeException('This promissory note can no longer be signed.');
            }

            if ($lockedNote->isPendingVerification()) {
                throw new \RuntimeException('This promissory note is already awaiting coordinator review.');
            }

            if (! $lockedNote->isPending()) {
                throw new \RuntimeException('This promissory note is not awaiting signature.');
            }

            if ($lockedNote->isSignatureOverdue()) {
                throw new \RuntimeException('This promissory note has already expired.');
            }

            PromissoryNote::whereKey($lockedNote->id)->update([
                'signed_at' => now(),
                'signed_by' => $student->id,
                'document_path' => $signatureFilePath,
                'status' => PromissoryNote::STATUS_PENDING_VERIFICATION,
                'open_student_id' => $student->id,
                'updated_at' => now(),
            ]);

            $lockedNote->refresh();

            if ($lockedNote->enrollment) {
                $lockedNote->enrollment->refreshFinancialStatus();
            }

            return $lockedNote->fresh(['student', 'enrollment', 'fees']);
        });
    }

    public function rejectSignature(PromissoryNote $note, ?string $reviewNotes = null): PromissoryNote
    {
        return DB::transaction(function () use ($note, $reviewNotes) {
            $lockedNote = PromissoryNote::whereKey($note->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedNote->isPendingVerification()) {
                throw new \RuntimeException('Only notes awaiting verification can be rejected.');
            }

            if ($lockedNote->document_path && Storage::disk('local')->exists($lockedNote->document_path)) {
                Storage::disk('local')->delete($lockedNote->document_path);
            }

            $lockedNote->forceFill([
                'status' => PromissoryNote::STATUS_PENDING_SIGNATURE,
                'signed_at' => null,
                'signed_by' => null,
                'document_path' => null,
            ]);

            if ($reviewNotes) {
                $lockedNote->notes = trim((($lockedNote->notes ?? '') !== '' ? $lockedNote->notes . "\n\n" : '') . 'Coordinator rejection: ' . $reviewNotes);
            }

            $lockedNote->save();

            return $lockedNote->fresh(['student', 'enrollment', 'fees']);
        });
    }

    public function checkSignatureDeadline(PromissoryNote $note, $currentDate = null): bool
    {
        $currentDate = $currentDate ? Carbon::parse($currentDate) : now();

        if (! $note->isPending()) {
            return false;
        }

        if ($note->signature_deadline < $currentDate && $note->isPending()) {
            $note->void();

            return true;
        }

        return false;
    }
}