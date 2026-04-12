<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when attempting to settle a promissory note that is already closed or locked
 */
class PromissoryNoteAlreadyClosedException extends PromissoryNoteSettlementException
{
    public function __construct(string $message = "This promissory note is already closed.", int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
