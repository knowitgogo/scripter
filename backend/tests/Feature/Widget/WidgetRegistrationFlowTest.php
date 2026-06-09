<?php

declare(strict_types=1);

namespace Tests\Feature\Widget;

use App\Enums\AuditAction;
use App\Enums\WidgetStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWidgets;
use Tests\TestCase;

/**
 * End-to-end admin widget registration integration tests.
 */
final class WidgetRegistrationFlowTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWidgets, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function admin_registers_widget_and_receives_widget_dto(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);

        $response = $this->actingAsJwt($admin)->postJson(
            '/api/v1/widgets',
            $this->widgetPayload([
                'name' => 'Feedback Form',
                'slug' => 'feedback-form',
            ]),
        );

        $response->assertCreated();
        $uuid = (string) $response->json('data.uuid');
        $this->assertNotEmpty($uuid);
        $response->assertJsonPath('data.name', 'Feedback Form');
        $response->assertJsonPath('data.slug', 'feedback-form');
        $response->assertJsonPath('data.status', WidgetStatus::Draft->value);

        $this->assertDatabaseHas('widgets', [
            'uuid' => $uuid,
            'name' => 'Feedback Form',
            'slug' => 'feedback-form',
            'status' => WidgetStatus::Draft->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_uuid' => $uuid,
        ]);
    }

    #[Test]
    public function admin_can_register_widget_with_explicit_status(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);

        $this->actingAsJwt($admin)->postJson(
            '/api/v1/widgets',
            $this->widgetPayload([
                'slug' => 'published-widget',
                'status' => WidgetStatus::Published->value,
            ]),
        )
            ->assertCreated()
            ->assertJsonPath('data.status', WidgetStatus::Published->value);
    }

    #[Test]
    public function registration_rejects_duplicate_slug(): void
    {
        $admin = $this->createAuthUser(['role_id' => $this->adminRole()->id]);
        $this->createWidgetFor($admin, ['slug' => 'feedback-form']);

        $this->actingAsJwt($admin)->postJson(
            '/api/v1/widgets',
            $this->widgetPayload(['slug' => 'feedback-form']),
        )->assertUnprocessable();
    }
}
