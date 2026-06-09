<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\WidgetVersionStatus;
use App\Models\Widget;
use App\Models\WidgetVersion;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetVersionModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widget_version_receives_uuid_on_creation(): void
    {
        $version = WidgetVersion::factory()->create();

        $this->assertNotEmpty($version->uuid);
        $this->assertTrue(UuidGenerator::isValid($version->uuid));
    }

    #[Test]
    public function widget_version_internal_id_and_widget_id_are_not_exposed_in_array(): void
    {
        $version = WidgetVersion::factory()->create();

        $array = $version->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('widget_id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function widget_version_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new WidgetVersion)->getRouteKeyName());
    }

    #[Test]
    public function widget_version_status_is_cast_to_enum(): void
    {
        $version = WidgetVersion::factory()->published()->create();

        $this->assertSame(WidgetVersionStatus::Published, $version->status);
        $this->assertSame('published', $version->toArray()['status']);
    }

    #[Test]
    public function widget_version_belongs_to_widget(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();
        $version = WidgetVersion::factory()->for($widget)->release('1.2.0')->create();

        $this->assertTrue($widget->is($version->widget));
        $this->assertDatabaseHas('widget_versions', [
            'uuid' => $version->uuid,
            'widget_id' => $widget->id,
            'version' => '1.2.0',
            'status' => WidgetVersionStatus::Published->value,
        ]);
    }

    #[Test]
    public function widget_has_many_versions(): void
    {
        $widget = Widget::factory()->create();
        WidgetVersion::factory()->for($widget)->count(2)->create();

        $this->assertCount(2, $widget->versions);
    }
}
