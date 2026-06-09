<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Widget;

use App\Models\Widget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWidgets;
use Tests\TestCase;

final class WidgetVersionAuthorizationEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWidgets, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function widget_version_publishing_endpoints_return_401_without_authentication(): void
    {
        $widget = Widget::factory()->create();
        $version = $this->createDraftWidgetVersion($widget);

        $this->postJson('/api/v1/widget-versions/'.$version->uuid.'/publish')->assertUnauthorized();
        $this->postJson('/api/v1/widget-versions/'.$version->uuid.'/deprecate')->assertUnauthorized();
        $this->postJson('/api/v1/widget-versions/'.$version->uuid.'/rollback')->assertUnauthorized();
    }

    #[Test]
    public function widget_version_publishing_endpoints_return_403_without_admin_widgets_publish_permission(): void
    {
        config(['permissions.roles.admin' => []]);

        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $version = $this->createDraftWidgetVersion($widget);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/publish')
            ->assertForbidden();

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/deprecate')
            ->assertForbidden();

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/rollback')
            ->assertForbidden();
    }

    #[Test]
    public function customer_cannot_publish_or_deprecate_widget_versions(): void
    {
        $customer = $this->createAuthUser();
        $widget = Widget::factory()->create();
        $version = $this->createDraftWidgetVersion($widget);

        $this->actingAsJwt($customer)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/publish')
            ->assertForbidden();

        $this->actingAsJwt($customer)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/deprecate')
            ->assertForbidden();

        $this->actingAsJwt($customer)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/rollback')
            ->assertForbidden();
    }
}
