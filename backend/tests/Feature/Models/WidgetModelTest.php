<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\WidgetStatus;
use App\Models\Widget;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widget_receives_uuid_on_creation(): void
    {
        $widget = Widget::factory()->create();

        $this->assertNotEmpty($widget->uuid);
        $this->assertTrue(UuidGenerator::isValid($widget->uuid));
    }

    #[Test]
    public function widget_internal_id_is_not_exposed_in_array(): void
    {
        $widget = Widget::factory()->create();

        $array = $widget->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function widget_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new Widget)->getRouteKeyName());
    }

    #[Test]
    public function widget_status_is_cast_to_enum(): void
    {
        $widget = Widget::factory()->published()->create();

        $this->assertSame(WidgetStatus::Published, $widget->status);
        $this->assertSame('published', $widget->toArray()['status']);
    }

    #[Test]
    public function widgets_table_persists_domain_fields(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();

        $this->assertDatabaseHas('widgets', [
            'uuid' => $widget->uuid,
            'name' => 'Feedback Form',
            'slug' => 'feedback-form',
            'status' => WidgetStatus::Published->value,
        ]);
    }
}
