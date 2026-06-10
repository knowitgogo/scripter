<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\SyncWidgetCategoriesDTO;
use App\Http\Requests\ApiRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SyncWidgetCategoriesDTOTest extends TestCase
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
                    'category_uuids' => [
                        '660e8400-e29b-41d4-a716-446655440001',
                        '770e8400-e29b-41d4-a716-446655440002',
                    ],
                ];
            }

            /**
             * @return array<string, mixed>
             */
            public function validated($key = null, $default = null): mixed
            {
                $data = $this->validationData();

                if ($key === null) {
                    return $data;
                }

                return $data[$key] ?? $default;
            }
        };

        $dto = SyncWidgetCategoriesDTO::fromRequest($request);

        $this->assertCount(2, $dto->category_uuids);
    }
}
