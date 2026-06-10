<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\WebsiteWidgetStatus;
use App\Models\Role;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteWidget;
use App\Models\Widget;
use App\Models\WidgetVersion;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteWidgetModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function website_widget_receives_uuid_on_creation(): void
    {
        $websiteWidget = WebsiteWidget::factory()->create();

        $this->assertNotEmpty($websiteWidget->uuid);
        $this->assertTrue(UuidGenerator::isValid($websiteWidget->uuid));
    }

    #[Test]
    public function website_widget_internal_ids_are_not_exposed_in_array(): void
    {
        $websiteWidget = WebsiteWidget::factory()->create();

        $array = $websiteWidget->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('website_id', $array);
        $this->assertArrayNotHasKey('widget_version_id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function website_widget_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new WebsiteWidget)->getRouteKeyName());
    }

    #[Test]
    public function website_widget_status_and_configuration_are_cast(): void
    {
        $websiteWidget = WebsiteWidget::factory()
            ->configured(['theme' => 'dark', 'position' => 'bottom-right'])
            ->active()
            ->create();

        $this->assertSame(WebsiteWidgetStatus::Active, $websiteWidget->status);
        $this->assertSame('dark', $websiteWidget->configuration_json['theme']);
        $this->assertSame('active', $websiteWidget->toArray()['status']);
        $this->assertSame('dark', $websiteWidget->toArray()['configuration_json']['theme']);
    }

    #[Test]
    public function website_widget_belongs_to_website_and_widget_version(): void
    {
        $website = Website::factory()->create();
        $widget = Widget::factory()->feedbackForm()->create();
        $version = WidgetVersion::factory()->for($widget)->release('1.0.0')->create();
        $websiteWidget = WebsiteWidget::factory()
            ->for($website)
            ->for($version)
            ->create();

        $this->assertTrue($website->is($websiteWidget->website));
        $this->assertTrue($version->is($websiteWidget->widgetVersion));
        $this->assertDatabaseHas('website_widgets', [
            'uuid' => $websiteWidget->uuid,
            'website_id' => $website->id,
            'widget_version_id' => $version->id,
            'status' => WebsiteWidgetStatus::Active->value,
        ]);
    }

    #[Test]
    public function website_has_many_website_widgets(): void
    {
        $website = Website::factory()->create();
        WebsiteWidget::factory()->for($website)->count(2)->create();

        $this->assertCount(2, $website->websiteWidgets);
    }

    #[Test]
    public function widget_version_has_many_website_widgets(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $version = WidgetVersion::factory()->published()->create();
        $firstWebsite = Website::factory()->create(['user_id' => $user->id]);
        $secondWebsite = Website::factory()->create(['user_id' => $user->id]);
        WebsiteWidget::factory()->for($firstWebsite)->for($version)->create();
        WebsiteWidget::factory()->for($secondWebsite)->for($version)->create();

        $this->assertCount(2, $version->websiteWidgets);
    }
}
