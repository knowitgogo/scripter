<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\InstallWidgetDTO;
use App\Http\Requests\ApiRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InstallWidgetDTOTest extends TestCase
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
                    'website_uuid' => '550e8400-e29b-41d4-a716-446655440000',
                    'widget_version_uuid' => '660e8400-e29b-41d4-a716-446655440001',
                    'configuration' => [
                        'theme' => 'dark',
                        'position' => 'bottom-right',
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

        $dto = InstallWidgetDTO::fromRequest($request);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $dto->website_uuid);
        $this->assertSame('660e8400-e29b-41d4-a716-446655440001', $dto->widget_version_uuid);
        $this->assertSame('dark', $dto->configuration['theme']);
    }

    #[Test]
    public function it_allows_missing_configuration(): void
    {
        $dto = new InstallWidgetDTO(
            website_uuid: '550e8400-e29b-41d4-a716-446655440000',
            widget_version_uuid: '660e8400-e29b-41d4-a716-446655440001',
        );

        $this->assertNull($dto->configuration);
        $this->assertNull($dto->toArray()['configuration']);
    }
}
