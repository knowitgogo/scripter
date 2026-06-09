<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiConfigTest extends TestCase
{
    #[Test]
    public function api_config_defines_versioning_strategy(): void
    {
        $this->assertSame(env('API_DEFAULT_VERSION', 'v1'), config('api.default_version'));
        $this->assertContains('v1', config('api.supported_versions'));
        $this->assertSame('X-API-Version', config('api.version_header'));
        $this->assertFileExists(base_path('routes/api/v1.php'));
    }
}
