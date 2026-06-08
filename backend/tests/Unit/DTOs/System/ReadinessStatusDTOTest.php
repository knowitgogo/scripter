<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\System;

use App\DTOs\System\ReadinessStatusDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReadinessStatusDTOTest extends TestCase
{
    #[Test]
    public function from_checks_marks_ready_when_all_ok(): void
    {
        $status = ReadinessStatusDTO::fromChecks([
            'database' => 'ok',
            'cache' => 'ok',
        ]);

        $this->assertTrue($status->isReady());
        $this->assertSame('ready', $status->status);
    }

    #[Test]
    public function from_checks_marks_not_ready_when_any_fail(): void
    {
        $status = ReadinessStatusDTO::fromChecks([
            'database' => 'ok',
            'cache' => 'fail',
        ]);

        $this->assertFalse($status->isReady());
        $this->assertSame('not_ready', $status->status);
    }
}
