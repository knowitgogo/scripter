<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\ApiVersion;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiVersionTest extends TestCase
{
    #[Test]
    public function it_returns_default_version_from_config(): void
    {
        config(['api.default_version' => 'v1']);

        $this->assertSame('v1', ApiVersion::default());
    }

    #[Test]
    public function it_returns_supported_versions_from_config(): void
    {
        config(['api.supported_versions' => ['v1']]);

        $this->assertSame(['v1'], ApiVersion::supported());
        $this->assertTrue(ApiVersion::isSupported('v1'));
        $this->assertFalse(ApiVersion::isSupported('v99'));
    }

    #[Test]
    public function it_builds_route_prefix(): void
    {
        config(['api.prefix' => 'api']);

        $this->assertSame('api/v1', ApiVersion::routePrefix('v1'));
    }

    #[Test]
    public function it_reads_version_from_request_attributes(): void
    {
        $request = Request::create('/api/v1/health', 'GET');
        $request->attributes->set('api_version', 'v1');

        $this->assertSame('v1', ApiVersion::fromRequest($request));
    }

    #[Test]
    public function it_falls_back_to_default_when_request_has_no_version(): void
    {
        config(['api.default_version' => 'v1']);

        $request = Request::create('/api/v1/health', 'GET');

        $this->assertSame('v1', ApiVersion::fromRequest($request));
    }
}
