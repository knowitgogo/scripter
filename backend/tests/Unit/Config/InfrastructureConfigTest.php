<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InfrastructureConfigTest extends TestCase
{
    #[Test]
    public function infrastructure_config_defines_cache_patterns_and_queue_names(): void
    {
        $this->assertSame(env('CACHE_STORE', 'database'), config('infrastructure.cache.store'));
        $this->assertSame(env('QUEUE_CONNECTION', 'database'), config('infrastructure.queue.connection'));
        $this->assertSame(
            'widget:config:{website_widget_uuid}',
            config('infrastructure.cache.patterns.widget_config'),
        );
        $this->assertSame('analytics', config('infrastructure.queue.names.analytics'));
        $this->assertSame('billing', config('infrastructure.queue.names.billing'));
    }
}
