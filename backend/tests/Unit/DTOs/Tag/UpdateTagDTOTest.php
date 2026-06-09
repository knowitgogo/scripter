<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Tag;

use App\DTOs\Tag\UpdateTagDTO;
use App\Http\Requests\ApiRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdateTagDTOTest extends TestCase
{
    #[Test]
    public function it_maps_validated_request_input(): void
    {
        $request = new class extends ApiRequest
        {
            public function authorize(): bool
            {
                return true;
            }

            /**
             * @return array<string, mixed>
             */
            public function validationData(): array
            {
                return [
                    'name' => 'Growth Marketing',
                    'slug' => 'growth-marketing',
                ];
            }

            /**
             * @return array<string, mixed>
             */
            public function validated($key = null, $default = null): array
            {
                return $this->validationData();
            }
        };

        $dto = UpdateTagDTO::fromRequest($request);

        $this->assertSame('Growth Marketing', $dto->name);
        $this->assertSame('growth-marketing', $dto->slug);
    }

    #[Test]
    public function it_builds_from_array(): void
    {
        $dto = UpdateTagDTO::fromArray([
            'name' => 'Analytics',
            'slug' => 'analytics',
        ]);

        $this->assertSame([
            'name' => 'Analytics',
            'slug' => 'analytics',
        ], $dto->toArray());
    }

    #[Test]
    public function it_requires_name_and_slug(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required DTO property [slug]');

        UpdateTagDTO::fromArray(['name' => 'Marketing']);
    }
}
