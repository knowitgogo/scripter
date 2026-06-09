<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Tag;

use App\DTOs\Tag\CreateTagDTO;
use App\Http\Requests\ApiRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CreateTagDTOTest extends TestCase
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
                    'name' => 'Marketing',
                    'slug' => 'marketing',
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

        $dto = CreateTagDTO::fromRequest($request);

        $this->assertSame('Marketing', $dto->name);
        $this->assertSame('marketing', $dto->slug);
    }

    #[Test]
    public function it_builds_from_array(): void
    {
        $dto = CreateTagDTO::fromArray([
            'name' => 'E-Commerce',
            'slug' => 'ecommerce',
        ]);

        $this->assertSame([
            'name' => 'E-Commerce',
            'slug' => 'ecommerce',
        ], $dto->toArray());
    }

    #[Test]
    public function it_requires_name_and_slug(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required DTO property [name]');

        CreateTagDTO::fromArray(['slug' => 'marketing']);
    }
}
