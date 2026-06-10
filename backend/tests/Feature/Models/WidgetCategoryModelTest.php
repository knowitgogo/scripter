<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Widget;
use App\Models\WidgetCategory;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetCategoryModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widget_category_receives_uuid_on_creation(): void
    {
        $category = WidgetCategory::factory()->create();

        $this->assertNotEmpty($category->uuid);
        $this->assertTrue(UuidGenerator::isValid($category->uuid));
    }

    #[Test]
    public function widget_category_internal_id_is_not_exposed_in_array(): void
    {
        $category = WidgetCategory::factory()->create();

        $array = $category->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function widget_category_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new WidgetCategory)->getRouteKeyName());
    }

    #[Test]
    public function widget_category_belongs_to_many_widgets(): void
    {
        $category = WidgetCategory::factory()->feedback()->create();
        $widget = Widget::factory()->feedbackForm()->create();
        $category->widgets()->attach($widget->id);

        $this->assertCount(1, $category->widgets);
        $this->assertTrue($widget->is($category->widgets->first()));
        $this->assertDatabaseHas('widget_category_widget', [
            'widget_id' => $widget->id,
            'widget_category_id' => $category->id,
        ]);
    }

    #[Test]
    public function widget_belongs_to_many_categories(): void
    {
        $widget = Widget::factory()->create();
        $category = WidgetCategory::factory()->analytics()->create();
        $widget->categories()->attach($category->id);

        $this->assertCount(1, $widget->categories);
        $this->assertSame('analytics', $widget->categories->first()->slug);
    }
}
