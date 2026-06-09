<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Widget;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWidgets;
use Tests\TestCase;

final class WidgetAuthorizationEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWidgets, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function widget_registration_returns_401_without_authentication(): void
    {
        $this->postJson('/api/v1/widgets', [])->assertUnauthorized();
    }

    #[Test]
    public function widget_registration_returns_403_without_admin_widgets_publish_permission(): void
    {
        config(['permissions.roles.admin' => []]);

        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widgets', $this->widgetPayload())
            ->assertForbidden();
    }

    #[Test]
    public function customer_cannot_register_widgets(): void
    {
        $customer = $this->createAuthUser();

        $this->actingAsJwt($customer)
            ->postJson('/api/v1/widgets', $this->widgetPayload())
            ->assertForbidden();
    }

    #[Test]
    public function widget_activation_endpoints_return_401_without_authentication(): void
    {
        $widget = $this->createWidgetFor($this->createAuthUser());

        $this->postJson('/api/v1/widgets/'.$widget->uuid.'/activate')->assertUnauthorized();
        $this->postJson('/api/v1/widgets/'.$widget->uuid.'/deactivate')->assertUnauthorized();
    }

    #[Test]
    public function widget_activation_endpoints_return_403_without_admin_widgets_publish_permission(): void
    {
        config(['permissions.roles.admin' => []]);

        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = $this->createWidgetFor($admin);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/activate')
            ->assertForbidden();

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/deactivate')
            ->assertForbidden();
    }

    #[Test]
    public function customer_cannot_activate_or_deactivate_widgets(): void
    {
        $customer = $this->createAuthUser();
        $widget = $this->createWidgetFor($customer);

        $this->actingAsJwt($customer)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/activate')
            ->assertForbidden();

        $this->actingAsJwt($customer)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/deactivate')
            ->assertForbidden();
    }
}
