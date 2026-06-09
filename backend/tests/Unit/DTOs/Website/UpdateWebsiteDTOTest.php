<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Website;

use App\DTOs\Website\UpdateWebsiteDTO;
use App\Http\Requests\ApiRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdateWebsiteDTOTest extends TestCase
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
                    'name' => 'Updated Site',
                    'url' => 'https://updated.example.com',
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

        $dto = UpdateWebsiteDTO::fromRequest($request);

        $this->assertSame('Updated Site', $dto->name);
        $this->assertSame('https://updated.example.com', $dto->url);
    }

    #[Test]
    public function it_requires_name_and_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required DTO property [name]');

        UpdateWebsiteDTO::fromArray(['url' => 'https://example.com']);
    }
}
