<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\WebsiteStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteStatusTest extends TestCase
{
    #[Test]
    public function it_exposes_canonical_status_values(): void
    {
        $this->assertSame('active', WebsiteStatus::Active->value);
        $this->assertSame('inactive', WebsiteStatus::Inactive->value);
        $this->assertSame('suspended', WebsiteStatus::Suspended->value);
    }
}
