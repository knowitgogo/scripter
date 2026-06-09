<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\RegisterWidgetDTO;
use App\Enums\WidgetStatus;
use App\Http\Requests\ApiRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterWidgetDTOTest extends TestCase
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
                    'name' => 'Feedback Form',
                    'slug' => 'feedback-form',
                    'description' => 'Collect feedback.',
                    'status' => 'draft',
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

        $dto = RegisterWidgetDTO::fromRequest($request);

        $this->assertSame('Feedback Form', $dto->name);
        $this->assertSame('feedback-form', $dto->slug);
        $this->assertSame('Collect feedback.', $dto->description);
        $this->assertSame(WidgetStatus::Draft, $dto->status);
    }
}
