<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Website;

use App\Enums\AuditAction;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\TestCase;

final class WebsiteCrudEndpointTest extends TestCase
{
    use InteractsWithAuthentication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function index_endpoint_returns_owned_websites(): void
    {
        $user = $this->createAuthUser();
        $otherUser = $this->createAuthUser();
        Website::factory()->create(['user_id' => $user->id, 'name' => 'Beta Site']);
        Website::factory()->create(['user_id' => $user->id, 'name' => 'Alpha Site']);
        Website::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/websites');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'errors' => [],
        ]);
        $response->assertJsonCount(2, 'data');
        $this->assertSame('Alpha Site', $response->json('data.0.name'));
        $this->assertSame('Beta Site', $response->json('data.1.name'));
        $this->assertArrayNotHasKey('user_id', $response->json('data.0'));
    }

    #[Test]
    public function store_endpoint_creates_website(): void
    {
        $user = $this->createAuthUser();

        $response = $this->actingAsJwt($user)->postJson('/api/v1/websites', [
            'name' => 'Acme Site',
            'url' => 'https://acme.example.com',
        ]);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Acme Site',
                'url' => 'https://acme.example.com',
                'status' => 'active',
            ],
            'errors' => [],
        ]);

        $this->assertDatabaseHas('websites', [
            'user_id' => $user->id,
            'name' => 'Acme Site',
            'url' => 'https://acme.example.com',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_type' => 'website',
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function show_endpoint_returns_owned_website(): void
    {
        $user = $this->createAuthUser();
        $website = Website::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/websites/'.$website->uuid);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'uuid' => $website->uuid,
            ],
            'errors' => [],
        ]);
    }

    #[Test]
    public function update_endpoint_updates_owned_website(): void
    {
        $user = $this->createAuthUser();
        $website = Website::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Name',
            'url' => 'https://old.example.com',
        ]);

        $response = $this->actingAsJwt($user)->putJson('/api/v1/websites/'.$website->uuid, [
            'name' => 'New Name',
            'url' => 'https://new.example.com',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'uuid' => $website->uuid,
                'name' => 'New Name',
                'url' => 'https://new.example.com',
            ],
            'errors' => [],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_uuid' => $website->uuid,
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function destroy_endpoint_deletes_owned_website(): void
    {
        $user = $this->createAuthUser();
        $website = Website::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($user)->deleteJson('/api/v1/websites/'.$website->uuid);

        $response->assertNoContent();
        $this->assertDatabaseMissing('websites', ['uuid' => $website->uuid]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deleted->value,
            'subject_uuid' => $website->uuid,
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function show_endpoint_returns_404_for_other_users_website(): void
    {
        $user = $this->createAuthUser();
        $otherUser = $this->createAuthUser();
        $website = Website::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/websites/'.$website->uuid)
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => [],
            ]);
    }

    #[Test]
    public function store_endpoint_returns_422_for_duplicate_url(): void
    {
        $user = $this->createAuthUser();
        Website::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://taken.example.com',
        ]);

        $this->actingAsJwt($user)
            ->postJson('/api/v1/websites', [
                'name' => 'Another Site',
                'url' => 'https://taken.example.com',
            ])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }

    #[Test]
    public function website_endpoints_return_401_without_authentication(): void
    {
        $user = $this->createAuthUser();
        $website = Website::factory()->create(['user_id' => $user->id]);

        $this->getJson('/api/v1/websites')->assertUnauthorized();
        $this->postJson('/api/v1/websites', [])->assertUnauthorized();
        $this->getJson('/api/v1/websites/'.$website->uuid)->assertUnauthorized();
        $this->putJson('/api/v1/websites/'.$website->uuid, [])->assertUnauthorized();
        $this->deleteJson('/api/v1/websites/'.$website->uuid)->assertUnauthorized();
    }

    #[Test]
    public function website_endpoints_return_403_without_required_permission(): void
    {
        config(['permissions.roles.customer' => []]);

        $user = $this->createAuthUser();
        $website = Website::factory()->create(['user_id' => $user->id]);

        $this->actingAsJwt($user)->getJson('/api/v1/websites')->assertForbidden();
        $this->actingAsJwt($user)->postJson('/api/v1/websites', [
            'name' => 'Blocked',
            'url' => 'https://blocked.example.com',
        ])->assertForbidden();
        $this->actingAsJwt($user)->getJson('/api/v1/websites/'.$website->uuid)->assertForbidden();
        $this->actingAsJwt($user)->putJson('/api/v1/websites/'.$website->uuid, [
            'name' => 'Blocked',
            'url' => 'https://blocked-update.example.com',
        ])->assertForbidden();
        $this->actingAsJwt($user)->deleteJson('/api/v1/websites/'.$website->uuid)->assertForbidden();
    }
}
