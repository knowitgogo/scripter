<?php

declare(strict_types=1);

namespace Tests\Feature\Widget;

use App\Enums\AuditAction;
use App\Enums\WidgetStatus;
use App\Models\Widget;
use App\Models\WidgetVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWidgets;
use Tests\TestCase;

/**
 * End-to-end admin widget activation/deactivation integration tests.
 */
final class WidgetActivationFlowTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWidgets, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function admin_activates_draft_widget_with_published_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = $this->createDraftWidgetWithPublishedVersion(['slug' => 'feedback-form']);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/activate')
            ->assertOk()
            ->assertJsonPath('data.status', WidgetStatus::Published->value);

        $this->assertDatabaseHas('widgets', [
            'uuid' => $widget->uuid,
            'status' => WidgetStatus::Published->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Published->value,
            'subject_uuid' => $widget->uuid,
        ]);
    }

    #[Test]
    public function admin_deactivates_published_widget(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->published()->create(['slug' => 'feedback-form']);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/deactivate')
            ->assertOk()
            ->assertJsonPath('data.status', WidgetStatus::Deprecated->value);

        $this->assertDatabaseHas('widgets', [
            'uuid' => $widget->uuid,
            'status' => WidgetStatus::Deprecated->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deprecated->value,
            'subject_uuid' => $widget->uuid,
        ]);
    }

    #[Test]
    public function admin_can_reactivate_deprecated_widget_with_published_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->deprecated()->create(['slug' => 'feedback-form']);
        WidgetVersion::factory()->for($widget)->release('1.0.0')->create();

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/activate')
            ->assertOk()
            ->assertJsonPath('data.status', WidgetStatus::Published->value);
    }

    #[Test]
    public function activation_fails_without_published_version(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->draft()->create(['slug' => 'no-version-widget']);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/activate')
            ->assertUnprocessable();
    }

    #[Test]
    public function deactivation_fails_for_draft_widget(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $widget = Widget::factory()->draft()->create(['slug' => 'draft-widget']);

        $this->actingAsJwt($admin)
            ->postJson('/api/v1/widgets/'.$widget->uuid.'/deactivate')
            ->assertUnprocessable();
    }
}
