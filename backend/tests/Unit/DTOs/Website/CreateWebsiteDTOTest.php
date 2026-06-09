<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Website;

use App\DTOs\Website\CreateWebsiteDTO;
use App\Http\Requests\ApiRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CreateWebsiteDTOTest extends TestCase
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
                    'name' => 'Acme Site',
                    'url' => 'https://acme.example.com',
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

        $dto = CreateWebsiteDTO::fromRequest($request);

        $this->assertSame('Acme Site', $dto->name);
        $this->assertSame('https://acme.example.com', $dto->url);
    }

    #[Test]
    public function it_builds_from_array(): void
    {
        $dto = CreateWebsiteDTO::fromArray([
            'name' => 'Beta Site',
            'url' => 'https://beta.example.com',
        ]);

        $this->assertSame([
            'name' => 'Beta Site',
            'url' => 'https://beta.example.com',
        ], $dto->toArray());
    }

    #[Test]
    public function it_requires_name_and_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required DTO property [name]');

        CreateWebsiteDTO::fromArray(['url' => 'https://example.com']);
    }
}
