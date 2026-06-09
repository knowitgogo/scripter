<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\DataTransferObject;

/**
 * Response payload for a successful logout.
 */
final class LogoutResultDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $message,
    ) {}

    public static function success(): self
    {
        return new self('Successfully logged out.');
    }
}
