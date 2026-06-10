<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\WebsiteWidgetStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteWidgetStatusTest extends TestCase
{
    #[Test]
    public function it_exposes_canonical_status_values(): void
    {
        $this->assertSame('active', WebsiteWidgetStatus::Active->value);
        $this->assertSame('inactive', WebsiteWidgetStatus::Inactive->value);
        $this->assertSame('suspended', WebsiteWidgetStatus::Suspended->value);
    }
}
