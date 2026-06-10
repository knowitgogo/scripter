<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\WidgetCategoriesDTO;
use App\DTOs\Widget\WidgetCategoryDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetCategoriesDTOTest extends TestCase
{
    #[Test]
    public function it_exposes_widget_uuid_and_categories_without_internal_ids(): void
    {
        $dto = WidgetCategoriesDTO::forWidget('550e8400-e29b-41d4-a716-446655440000', [
            new WidgetCategoryDTO(
                uuid: '660e8400-e29b-41d4-a716-446655440001',
                name: 'Feedback',
                slug: 'feedback',
            ),
        ]);

        $array = $dto->toArray();

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $dto->widget_uuid);
        $this->assertCount(1, $dto->categories);
        $this->assertSame('feedback', $array['categories'][0]['slug']);
    }
}
