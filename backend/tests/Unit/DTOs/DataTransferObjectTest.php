<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use App\DTOs\DataTransferObject;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DataTransferObjectTest extends TestCase
{
    #[Test]
    public function to_array_returns_public_properties(): void
    {
        $dto = new class ('acme', 'active') extends DataTransferObject
        {
            public function __construct(
                public readonly string $name,
                public readonly string $status,
            ) {}
        };

        $this->assertSame([
            'name' => 'acme',
            'status' => 'active',
        ], $dto->toArray());
    }

    #[Test]
    public function json_serialize_matches_to_array(): void
    {
        $dto = new class ('v1') extends DataTransferObject
        {
            public function __construct(
                public readonly string $version,
            ) {}
        };

        $this->assertSame($dto->toArray(), $dto->jsonSerialize());
    }
}
