<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\UpdateWebsiteWidgetDTO;
use App\Enums\WebsiteWidgetStatus;
use App\Http\Requests\ApiRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdateWebsiteWidgetDTOTest extends TestCase
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
                    'status' => 'inactive',
                    'configuration' => [
                        'theme' => 'light',
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

        $dto = UpdateWebsiteWidgetDTO::fromRequest($request);

        $this->assertSame(WebsiteWidgetStatus::Inactive, $dto->status);
        $this->assertSame('light', $dto->configuration['theme']);
        $this->assertSame('inactive', $dto->toArray()['status']);
    }

    #[Test]
    public function it_allows_partial_updates(): void
    {
        $dto = new UpdateWebsiteWidgetDTO(status: WebsiteWidgetStatus::Suspended);

        $array = $dto->toArray();

        $this->assertSame('suspended', $array['status']);
        $this->assertNull($array['configuration']);
    }
}
