<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Cache;

use App\Support\Cache\CacheKeyBuilder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CacheKeyBuilderTest extends TestCase
{
    #[Test]
    public function it_builds_keys_from_pattern_and_params(): void
    {
        $key = CacheKeyBuilder::build(
            'widget:config:{website_widget_uuid}',
            ['website_widget_uuid' => '550e8400-e29b-41d4-a716-446655440000'],
        );

        $this->assertSame('widget:config:550e8400-e29b-41d4-a716-446655440000', $key);
    }

    #[Test]
    public function it_resolves_configured_patterns_and_ttls(): void
    {
        $this->assertSame(
            'widget:catalog:published',
            CacheKeyBuilder::pattern('widget_catalog'),
        );
        $this->assertSame(900, CacheKeyBuilder::ttl('widget_config'));
    }
}
