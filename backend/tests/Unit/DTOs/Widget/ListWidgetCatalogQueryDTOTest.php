<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\ListWidgetCatalogQueryDTO;
use App\Http\Requests\ApiRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListWidgetCatalogQueryDTOTest extends TestCase
{
    #[Test]
    public function it_defaults_to_no_filters(): void
    {
        $dto = new ListWidgetCatalogQueryDTO;

        $this->assertNull($dto->search);
        $this->assertNull($dto->category);
        $this->assertSame([], $dto->slugs);
        $this->assertFalse($dto->hasSearch());
        $this->assertFalse($dto->hasCategoryFilter());
        $this->assertFalse($dto->hasSlugFilter());
    }

    #[Test]
    public function it_treats_blank_search_and_category_as_no_filter(): void
    {
        $dto = new ListWidgetCatalogQueryDTO(search: '   ', category: '   ');

        $this->assertFalse($dto->hasSearch());
        $this->assertFalse($dto->hasCategoryFilter());
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
                    'search' => ' feedback ',
                    'category' => ' analytics ',
                    'slugs' => ['feedback-form', 'newsletter-signup'],
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

        $dto = ListWidgetCatalogQueryDTO::fromRequest($request);

        $this->assertSame('feedback', $dto->normalizedSearch());
        $this->assertSame('analytics', $dto->normalizedCategory());
        $this->assertTrue($dto->hasSlugFilter());
        $this->assertSame(['feedback-form', 'newsletter-signup'], $dto->slugs);
    }
}
