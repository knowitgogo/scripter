<?php

declare(strict_types=1);

namespace Tests\Feature\Widget;

use App\Enums\AuditAction;
use App\Enums\WidgetVersionStatus;
use App\Models\Widget;
use App\Models\WidgetVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWidgets;
use Tests\TestCase;

/**
 * End-to-end admin widget version publishing integration tests.
 */
final class WidgetVersionPublishingFlowTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWidgets, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function admin_publishes_draft_widget_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->draft()->create(['slug' => 'feedback-form']);
        $version = $this->createDraftWidgetVersion($widget, '1.0.0');

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/publish')
            ->assertOk()
            ->assertJsonPath('data.status', WidgetVersionStatus::Published->value)
            ->assertJsonPath('data.version', '1.0.0');

        $this->assertDatabaseHas('widget_versions', [
            'uuid' => $version->uuid,
            'status' => WidgetVersionStatus::Published->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Published->value,
            'subject_uuid' => $version->uuid,
        ]);
    }

    #[Test]
    public function publishing_new_version_deprecates_previous_published_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $existing = WidgetVersion::factory()->for($widget)->release('1.0.0')->create();
        $next = $this->createDraftWidgetVersion($widget, '1.1.0');

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$next->uuid.'/publish')
            ->assertOk();

        $this->assertDatabaseHas('widget_versions', [
            'uuid' => $existing->uuid,
            'status' => WidgetVersionStatus::Deprecated->value,
        ]);
        $this->assertDatabaseHas('widget_versions', [
            'uuid' => $next->uuid,
            'status' => WidgetVersionStatus::Published->value,
        ]);
    }

    #[Test]
    public function admin_deprecates_published_widget_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $version = WidgetVersion::factory()->for($widget)->release('1.0.0')->create();

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/deprecate')
            ->assertOk()
            ->assertJsonPath('data.status', WidgetVersionStatus::Deprecated->value);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deprecated->value,
            'subject_uuid' => $version->uuid,
        ]);
    }

    #[Test]
    public function publish_fails_without_asset_manifest_url(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $version = $this->createDraftWidgetVersion($widget, '1.0.0', assetManifestUrl: null);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/publish')
            ->assertUnprocessable();
    }

    #[Test]
    public function deprecate_fails_for_draft_widget_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $version = $this->createDraftWidgetVersion($widget, '1.0.0');

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/deprecate')
            ->assertUnprocessable();
    }
}
