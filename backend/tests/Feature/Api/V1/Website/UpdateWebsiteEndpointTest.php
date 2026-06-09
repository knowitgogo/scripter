<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Website;

use App\Enums\AuditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWebsites;
use Tests\TestCase;

final class UpdateWebsiteEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWebsites, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function update_endpoint_updates_owned_website(): void
    {
        $user = $this->createAuthUser();
        $website = $this->createWebsiteFor($user, [
            'name' => 'Old Name',
            'url' => 'https://old.example.com',
        ]);

        $response = $this->actingAsJwt($user)->putJson('/api/v1/websites/'.$website->uuid, [
            'name' => 'New Name',
            'url' => 'https://new.example.com',
        ]);

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJson([
            'data' => [
                'uuid' => $website->uuid,
                'name' => 'New Name',
                'url' => 'https://new.example.com',
            ],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_uuid' => $website->uuid,
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function update_endpoint_returns_404_for_other_users_website(): void
    {
        $user = $this->createAuthUser();
        $otherUser = $this->createAuthUser();
        $website = $this->createWebsiteFor($otherUser);

        $this->actingAsJwt($user)
            ->putJson('/api/v1/websites/'.$website->uuid, [
                'name' => 'Blocked',
                'url' => 'https://blocked.example.com',
            ])
            ->assertNotFound();
    }

    #[Test]
    public function update_endpoint_returns_422_for_duplicate_url(): void
    {
        $user = $this->createAuthUser();
        $this->createWebsiteFor($user, ['url' => 'https://taken.example.com']);
        $website = $this->createWebsiteFor($user, ['url' => 'https://mine.example.com']);

        $this->actingAsJwt($user)
            ->putJson('/api/v1/websites/'.$website->uuid, [
                'name' => 'Updated',
                'url' => 'https://taken.example.com',
            ])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }

    #[Test]
    public function update_endpoint_returns_422_for_missing_fields(): void
    {
        $user = $this->createAuthUser();
        $website = $this->createWebsiteFor($user);

        $this->actingAsJwt($user)
            ->putJson('/api/v1/websites/'.$website->uuid, [])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }
}
