<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

/**
 * Thrown when attempting to settle on a closed or voided promissory note
 */
class PromissoryNoteSettlementException extends PromissoryNoteException
{
    public function __construct(string $message = "Cannot settle this promissory note.", int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request)
    {
        return response()->json([
            'message' => $this->message,
            'error_type' => 'settlement_error'
        ], $this->code);
    }
}
