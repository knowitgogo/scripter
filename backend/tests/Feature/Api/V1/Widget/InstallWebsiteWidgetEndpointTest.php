<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Widget;

use App\Enums\AuditAction;
use App\Enums\WebsiteWidgetStatus;
use App\Models\WebsiteWidget;
use App\Models\WidgetVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWebsites;
use Tests\Concerns\InteractsWithWidgets;
use Tests\TestCase;

final class InstallWebsiteWidgetEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWebsites, InteractsWithWidgets, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function install_endpoint_creates_website_widget_installation(): void
    {
        $user = $this->createAuthUser();
        $website = $this->createWebsiteFor($user);
        $version = $this->createPublishedWidgetVersion();

        $response = $this->actingAsJwt($user)->postJson(
            '/api/v1/website-widgets',
            $this->installWidgetPayload($website, $version),
        );

        $response->assertCreated();
        $this->assertSuccessfulApiEnvelope($response, 201);
        $response->assertJson([
            'data' => [
                'website_uuid' => $website->uuid,
                'widget_version_uuid' => $version->uuid,
                'status' => WebsiteWidgetStatus::Active->value,
                'configuration' => [
                    'theme' => 'dark',
                    'position' => 'bottom-right',
                ],
            ],
        ]);
        $response->assertJsonStructure([
            'data' => [
                'uuid',
                'website_uuid',
                'widget_version_uuid',
                'status',
                'configuration',
                'created_at',
                'updated_at',
            ],
        ]);

        $this->assertDatabaseHas('website_widgets', [
            'website_id' => $website->id,
            'widget_version_id' => $version->id,
            'status' => WebsiteWidgetStatus::Active->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_type' => 'website_widget',
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function install_endpoint_returns_404_for_website_not_owned_by_user(): void
    {
        $owner = $this->createAuthUser();
        $otherUser = $this->createAuthUser();
        $website = $this->createWebsiteFor($owner);
        $version = $this->createPublishedWidgetVersion();

        $this->actingAsJwt($otherUser)
            ->postJson('/api/v1/website-widgets', $this->installWidgetPayload($website, $version))
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
            ]);
    }

    #[Test]
    public function install_endpoint_returns_422_for_unpublished_widget_version(): void
    {
        $user = $this->createAuthUser();
        $website = $this->createWebsiteFor($user);
        $version = WidgetVersion::factory()->draft()->create();

        $this->actingAsJwt($user)
            ->postJson('/api/v1/website-widgets', $this->installWidgetPayload($website, $version))
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Only published widget versions can be installed.',
            ]);
    }

    #[Test]
    public function install_endpoint_returns_422_when_widget_version_is_already_installed(): void
    {
        $user = $this->createAuthUser();
        $website = $this->createWebsiteFor($user);
        $version = $this->createPublishedWidgetVersion();
        WebsiteWidget::factory()->for($website)->for($version)->create();

        $this->actingAsJwt($user)
            ->postJson('/api/v1/website-widgets', $this->installWidgetPayload($website, $version))
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'This widget version is already installed on the website.',
            ]);
    }

    #[Test]
    public function install_endpoint_returns_422_for_missing_fields(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->postJson('/api/v1/website-widgets', [])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonStructure(['errors']);
    }

    #[Test]
    public function install_endpoint_returns_422_for_unknown_uuids(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->postJson('/api/v1/website-widgets', [
                'website_uuid' => '00000000-0000-0000-0000-000000000000',
                'widget_version_uuid' => '00000000-0000-0000-0000-000000000001',
            ])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }
}
