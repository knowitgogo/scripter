<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\AssignWidgetTemplateDTO;
use App\Http\Requests\ApiRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AssignWidgetTemplateDTOTest extends TestCase
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
                    'name' => 'Embedded Script',
                    'slug' => 'embedded',
                    'description' => 'Standard script-tag embed snippet.',
                    'content' => '<script src="{{cdn_url}}"></script>',
                    'is_default' => true,
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

        $dto = AssignWidgetTemplateDTO::fromRequest($request);

        $this->assertSame('Embedded Script', $dto->name);
        $this->assertSame('embedded', $dto->slug);
        $this->assertTrue($dto->is_default);
    }
}
