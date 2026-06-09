<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Auth;

use App\DTOs\Auth\AuthTokenDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AuthTokenDTOTest extends TestCase
{
    #[Test]
    public function it_builds_token_payload_from_jwt(): void
    {
        $dto = AuthTokenDTO::fromJwt('jwt-token-value', 60);

        $this->assertSame([
            'access_token' => 'jwt-token-value',
            'token_type' => 'bearer',
            'expires_in' => 3600,
        ], $dto->toArray());
    }
}
