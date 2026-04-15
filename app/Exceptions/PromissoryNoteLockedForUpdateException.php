<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

/**
 * Thrown when row lock times out during concurrent settlement attempts
 */
class PromissoryNoteLockedForUpdateException extends PromissoryNoteSettlementException
{
    public function __construct(string $message = "Promissory note is locked for update. Another user may be processing a payment. Please try again.", int $code = 409, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request)
    {
        return response()->json([
            'message' => $this->message,
            'error_type' => 'lock_timeout',
            'retry_after' => 5 // seconds
        ], $this->code);
    }
}
