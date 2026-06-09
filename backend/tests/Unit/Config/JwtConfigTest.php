<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class JwtConfigTest extends TestCase
{
    #[Test]
    public function jwt_config_is_loaded_with_expected_defaults(): void
    {
        $this->assertNotEmpty(config('jwt.secret'));
        $this->assertSame(60, config('jwt.ttl'));
        $this->assertSame(20160, config('jwt.refresh_ttl'));
        $this->assertSame('HS256', config('jwt.algo'));
        $this->assertTrue((bool) config('jwt.blacklist_enabled'));
        $this->assertSame(['role'], config('jwt.persistent_claims'));
    }

    #[Test]
    public function api_guard_uses_jwt_driver(): void
    {
        $this->assertSame('api', config('auth.defaults.guard'));
        $this->assertSame('jwt', config('auth.guards.api.driver'));
        $this->assertSame('users', config('auth.guards.api.provider'));
    }
}
