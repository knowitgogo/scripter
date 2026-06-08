<?php

declare(strict_types=1);

namespace Tests\Unit\Services\System;

use App\DTOs\System\HealthStatusDTO;
use App\Services\System\HealthCheckService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HealthCheckServiceTest extends TestCase
{
    #[Test]
    public function it_returns_alive_status(): void
    {
        $status = (new HealthCheckService)->check();

        $this->assertInstanceOf(HealthStatusDTO::class, $status);
        $this->assertSame([
            'status' => 'ok',
            'version' => 'v1',
        ], $status->toArray());
    }
}
