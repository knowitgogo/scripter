<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\WebsiteWidgetDTO;
use App\Enums\WebsiteWidgetStatus;
use App\Models\Role;
use App\Models\Website;
use App\Models\WebsiteWidget;
use App\Models\Widget;
use App\Models\WidgetVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteWidgetDTOTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_website_widget_model_to_dto_without_internal_ids(): void
    {
        $website = Website::factory()->create();
        $widget = Widget::factory()->feedbackForm()->create();
        $version = WidgetVersion::factory()->for($widget)->release('1.0.0')->create();
        $websiteWidget = WebsiteWidget::factory()
            ->for($website)
            ->for($version)
            ->configured(['theme' => 'dark', 'position' => 'bottom-right'])
            ->active()
            ->create();

        $dto = WebsiteWidgetDTO::fromModel($websiteWidget);
        $array = $dto->toArray();

        $this->assertSame($websiteWidget->uuid, $dto->uuid);
        $this->assertSame($website->uuid, $dto->website_uuid);
        $this->assertSame($version->uuid, $dto->widget_version_uuid);
        $this->assertSame(WebsiteWidgetStatus::Active, $dto->status);
        $this->assertSame('dark', $dto->configuration['theme']);
        $this->assertSame('active', $array['status']);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('website_id', $array);
        $this->assertArrayNotHasKey('widget_version_id', $array);
    }

    #[Test]
    public function it_rejects_non_website_widget_models(): void
    {
        $role = Role::factory()->customer()->create();

        $this->expectException(\InvalidArgumentException::class);

        WebsiteWidgetDTO::fromModel($role);
    }
}
