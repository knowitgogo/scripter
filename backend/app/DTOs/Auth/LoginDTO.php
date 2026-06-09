<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;

/**
 * Credentials payload for user authentication.
 */
final class LoginDTO extends DataTransferObject
{
    use MapsFromRequest;

    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
