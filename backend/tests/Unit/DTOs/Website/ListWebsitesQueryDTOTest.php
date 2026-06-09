<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Website;

use App\DTOs\Website\ListWebsitesQueryDTO;
use App\Http\Requests\ApiRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListWebsitesQueryDTOTest extends TestCase
{
    #[Test]
    public function it_defaults_to_no_tag_filter(): void
    {
        $dto = new ListWebsitesQueryDTO;

        $this->assertSame([], $dto->tag_uuids);
        $this->assertFalse($dto->hasTagFilter());
    }

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
                    'tag_uuids' => [
                        '550e8400-e29b-41d4-a716-446655440000',
                        '660e8400-e29b-41d4-a716-446655440001',
                    ],
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

        $dto = ListWebsitesQueryDTO::fromRequest($request);

        $this->assertTrue($dto->hasTagFilter());
        $this->assertCount(2, $dto->tag_uuids);
    }
}
