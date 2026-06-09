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
 * End-to-end admin widget version rollback integration tests.
 */
final class WidgetVersionRollbackFlowTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWidgets, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function admin_rolls_back_to_deprecated_widget_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $previous = WidgetVersion::factory()->for($widget)->deprecated()->create([
            'version' => '1.0.0',
            'asset_manifest_url' => 'https://cdn.example.com/widgets/1.0.0/manifest.json',
        ]);
        $current = WidgetVersion::factory()->for($widget)->release('1.1.0')->create();

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$previous->uuid.'/rollback')
            ->assertOk()
            ->assertJsonPath('data.status', WidgetVersionStatus::Published->value)
            ->assertJsonPath('data.version', '1.0.0');

        $this->assertDatabaseHas('widget_versions', [
            'uuid' => $previous->uuid,
            'status' => WidgetVersionStatus::Published->value,
        ]);
        $this->assertDatabaseHas('widget_versions', [
            'uuid' => $current->uuid,
            'status' => WidgetVersionStatus::Deprecated->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Restored->value,
            'subject_uuid' => $previous->uuid,
        ]);
    }

    #[Test]
    public function rollback_fails_for_draft_widget_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $version = $this->createDraftWidgetVersion($widget, '1.0.0');

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/rollback')
            ->assertUnprocessable();
    }

    #[Test]
    public function rollback_fails_for_published_widget_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $version = WidgetVersion::factory()->for($widget)->release('1.0.0')->create();

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/rollback')
            ->assertUnprocessable();
    }

    #[Test]
    public function rollback_fails_without_asset_manifest_url(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->create();
        $version = WidgetVersion::factory()->for($widget)->deprecated()->create([
            'version' => '1.0.0',
            'asset_manifest_url' => null,
        ]);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widget-versions/'.$version->uuid.'/rollback')
            ->assertUnprocessable();
    }
}
