<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Widget;
use App\Models\WidgetTemplate;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetTemplateModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widget_template_receives_uuid_on_creation(): void
    {
        $template = WidgetTemplate::factory()->create();

        $this->assertNotEmpty($template->uuid);
        $this->assertTrue(UuidGenerator::isValid($template->uuid));
    }

    #[Test]
    public function widget_template_internal_id_and_widget_id_are_not_exposed_in_array(): void
    {
        $template = WidgetTemplate::factory()->create();

        $array = $template->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('widget_id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function widget_template_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new WidgetTemplate)->getRouteKeyName());
    }

    #[Test]
    public function widget_template_is_default_is_cast_to_boolean(): void
    {
        $template = WidgetTemplate::factory()->defaultTemplate()->create();

        $this->assertTrue($template->is_default);
        $this->assertTrue($template->toArray()['is_default']);
    }

    #[Test]
    public function widget_template_belongs_to_widget(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();
        $template = WidgetTemplate::factory()->for($widget)->embedded()->create();

        $this->assertTrue($widget->is($template->widget));
        $this->assertDatabaseHas('widget_templates', [
            'uuid' => $template->uuid,
            'widget_id' => $widget->id,
            'slug' => 'embedded',
            'is_default' => false,
        ]);
    }

    #[Test]
    public function widget_has_many_templates(): void
    {
        $widget = Widget::factory()->create();
        WidgetTemplate::factory()->for($widget)->embedded()->create();
        WidgetTemplate::factory()->for($widget)->hosted()->create();

        $this->assertCount(2, $widget->templates);
    }
}
