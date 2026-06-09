<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\WidgetStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetStatusTest extends TestCase
{
    #[Test]
    public function it_exposes_canonical_status_values(): void
    {
        $this->assertSame('draft', WidgetStatus::Draft->value);
        $this->assertSame('published', WidgetStatus::Published->value);
        $this->assertSame('deprecated', WidgetStatus::Deprecated->value);
    }
}
