<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when settlement amount does not align with selected fees
 */
class PromissoryNotePartialAllocationException extends PromissoryNoteSettlementException
{
    public function __construct(string $message = "Settlement amount does not align with selected fees.", int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
