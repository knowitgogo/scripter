<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Base exception for domain rule violations surfaced by Services.
 */
class DomainException extends Exception
{
    public function __construct(
        string $message = 'A domain rule was violated.',
        protected int $statusCode = 422,
        ?Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
