<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\DataTransferObject;

/**
 * JWT access token payload returned by Auth domain services.
 */
final class AuthTokenDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $access_token,
        public readonly string $token_type,
        public readonly int $expires_in,
    ) {}

    public static function fromJwt(string $accessToken, int $expiresInMinutes): self
    {
        return new self(
            access_token: $accessToken,
            token_type: 'bearer',
            expires_in: $expiresInMinutes * 60,
        );
    }
}
