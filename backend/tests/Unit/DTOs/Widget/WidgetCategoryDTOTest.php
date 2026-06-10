<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\WidgetCategoryDTO;
use App\Models\Role;
use App\Models\WidgetCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetCategoryDTOTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_widget_category_model_to_dto_without_internal_ids(): void
    {
        $category = WidgetCategory::factory()->feedback()->create();

        $dto = WidgetCategoryDTO::fromModel($category);
        $array = $dto->toArray();

        $this->assertSame($category->uuid, $dto->uuid);
        $this->assertSame('Feedback', $dto->name);
        $this->assertSame('feedback', $dto->slug);
        $this->assertSame('Widgets for collecting user feedback and surveys.', $dto->description);
        $this->assertArrayNotHasKey('id', $array);
    }

    #[Test]
    public function it_rejects_non_widget_category_models(): void
    {
        $role = Role::factory()->customer()->create();

        $this->expectException(\InvalidArgumentException::class);

        WidgetCategoryDTO::fromModel($role);
    }
}
