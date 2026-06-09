<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\WidgetVersionStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetVersionStatusTest extends TestCase
{
    #[Test]
    public function it_exposes_canonical_status_values(): void
    {
        $this->assertSame('draft', WidgetVersionStatus::Draft->value);
        $this->assertSame('published', WidgetVersionStatus::Published->value);
        $this->assertSame('deprecated', WidgetVersionStatus::Deprecated->value);
    }
}
