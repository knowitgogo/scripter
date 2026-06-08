<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Cache;

use App\Repositories\Cache\LaravelCacheRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LaravelCacheRepositoryTest extends TestCase
{
    private LaravelCacheRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new LaravelCacheRepository;
    }

    #[Test]
    public function it_stores_and_retrieves_values(): void
    {
        $this->repository->put('test:key', ['value' => 1], 60, 'array');

        $this->assertSame(['value' => 1], $this->repository->get('test:key', store: 'array'));
    }

    #[Test]
    public function it_remembers_values(): void
    {
        $calls = 0;

        $first = $this->repository->remember('test:remember', 60, function () use (&$calls) {
            $calls++;

            return 'cached-value';
        }, 'array');

        $second = $this->repository->remember('test:remember', 60, function () use (&$calls) {
            $calls++;

            return 'cached-value';
        }, 'array');

        $this->assertSame('cached-value', $first);
        $this->assertSame('cached-value', $second);
        $this->assertSame(1, $calls);
    }

    #[Test]
    public function it_forgets_values(): void
    {
        $this->repository->put('test:forget', true, 60, 'array');

        $this->assertTrue($this->repository->forget('test:forget', 'array'));
        $this->assertFalse($this->repository->has('test:forget', 'array'));
    }
}
