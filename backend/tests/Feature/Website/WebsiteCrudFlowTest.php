<?php

declare(strict_types=1);

namespace Tests\Feature\Website;

use App\Enums\AuditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWebsites;
use Tests\TestCase;

/**
 * End-to-end website CRUD lifecycle integration tests.
 */
final class WebsiteCrudFlowTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWebsites, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function full_crud_lifecycle_create_list_show_update_delete(): void
    {
        $user = $this->createAuthUser();

        $createResponse = $this->actingAsJwt($user)->postJson(
            '/api/v1/websites',
            $this->websitePayload([
                'name' => 'Flow Site',
                'url' => 'https://flow.example.com',
            ]),
        );

        $createResponse->assertCreated();
        $uuid = (string) $createResponse->json('data.uuid');
        $this->assertNotEmpty($uuid);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/websites')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $uuid);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/websites/'.$uuid)
            ->assertOk()
            ->assertJsonPath('data.name', 'Flow Site');

        $this->actingAsJwt($user)
            ->putJson('/api/v1/websites/'.$uuid, [
                'name' => 'Flow Site Updated',
                'url' => 'https://flow-updated.example.com',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Flow Site Updated');

        $this->actingAsJwt($user)
            ->deleteJson('/api/v1/websites/'.$uuid)
            ->assertNoContent();

        $this->assertDatabaseMissing('websites', ['uuid' => $uuid]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_uuid' => $uuid,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_uuid' => $uuid,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deleted->value,
            'subject_uuid' => $uuid,
        ]);
    }
}
