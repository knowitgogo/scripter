<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\UuidGenerator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UuidGeneratorTest extends TestCase
{
    #[Test]
    public function generate_returns_valid_uuid(): void
    {
        $uuid = UuidGenerator::generate();

        $this->assertTrue(UuidGenerator::isValid($uuid));
    }

    #[Test]
    public function is_valid_rejects_invalid_values(): void
    {
        $this->assertFalse(UuidGenerator::isValid('not-a-uuid'));
        $this->assertFalse(UuidGenerator::isValid(''));
    }
}
