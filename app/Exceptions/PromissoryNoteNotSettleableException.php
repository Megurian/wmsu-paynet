<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

/**
 * Thrown when PN cannot accept settlements due to status
 */
class PromissoryNoteNotSettleableException extends PromissoryNoteSettlementException
{
    public function __construct(string $message = "This promissory note cannot accept settlements in its current status.", int $code = 428, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request)
    {
        return response()->json([
            'message' => $this->message,
            'error_type' => 'not_settleable',
            'required_precondition' => 'PN must be ACTIVE, DEFAULT, or BAD_DEBT with remaining balance'
        ], $this->code);
    }
}
