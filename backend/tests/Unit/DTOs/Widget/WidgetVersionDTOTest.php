<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\WidgetVersionDTO;
use App\Enums\WidgetVersionStatus;
use App\Models\Role;
use App\Models\Widget;
use App\Models\WidgetVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetVersionDTOTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_widget_version_model_to_dto_without_internal_ids(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();
        $version = WidgetVersion::factory()
            ->for($widget)
            ->release('1.2.0')
            ->create();

        $dto = WidgetVersionDTO::fromModel($version);
        $array = $dto->toArray();

        $this->assertSame($version->uuid, $dto->uuid);
        $this->assertSame($widget->uuid, $dto->widget_uuid);
        $this->assertSame('1.2.0', $dto->version);
        $this->assertSame(WidgetVersionStatus::Published, $dto->status);
        $this->assertSame(
            'https://cdn.example.com/widgets/feedback-form/1.2.0/manifest.json',
            $dto->asset_manifest_url,
        );
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('widget_id', $array);
        $this->assertSame('published', $array['status']);
    }

    #[Test]
    public function it_rejects_non_widget_version_models(): void
    {
        $role = Role::factory()->customer()->create();

        $this->expectException(\InvalidArgumentException::class);

        WidgetVersionDTO::fromModel($role);
    }
}
