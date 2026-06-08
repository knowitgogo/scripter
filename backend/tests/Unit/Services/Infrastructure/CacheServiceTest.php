<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Infrastructure;

use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Services\Infrastructure\CacheService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CacheServiceTest extends TestCase
{
    #[Test]
    public function it_remembers_using_configured_pattern_and_ttl(): void
    {
        $cache = $this->createMock(CacheRepositoryInterface::class);
        $cache->expects($this->once())
            ->method('remember')
            ->with(
                'widget:config:550e8400-e29b-41d4-a716-446655440000',
                900,
                $this->isCallable(),
            )
            ->willReturn(['theme' => 'dark']);

        $value = (new CacheService($cache))->remember(
            'widget_config',
            ['website_widget_uuid' => '550e8400-e29b-41d4-a716-446655440000'],
            fn (): array => ['theme' => 'dark'],
        );

        $this->assertSame(['theme' => 'dark'], $value);
    }

    #[Test]
    public function it_forgets_using_configured_pattern(): void
    {
        $cache = $this->createMock(CacheRepositoryInterface::class);
        $cache->expects($this->once())
            ->method('forget')
            ->with('plan:limits:550e8400-e29b-41d4-a716-446655440000')
            ->willReturn(true);

        $result = (new CacheService($cache))->forget(
            'plan_limits',
            ['user_uuid' => '550e8400-e29b-41d4-a716-446655440000'],
        );

        $this->assertTrue($result);
    }
}
