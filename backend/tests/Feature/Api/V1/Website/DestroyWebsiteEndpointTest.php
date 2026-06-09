<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Website;

use App\Enums\AuditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWebsites;
use Tests\TestCase;

final class DestroyWebsiteEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWebsites, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function destroy_endpoint_deletes_owned_website(): void
    {
        $user = $this->createAuthUser();
        $website = $this->createWebsiteFor($user);

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
    public function destroy_endpoint_returns_404_for_other_users_website(): void
    {
        $user = $this->createAuthUser();
        $otherUser = $this->createAuthUser();
        $website = $this->createWebsiteFor($otherUser);

        $this->actingAsJwt($user)
            ->deleteJson('/api/v1/websites/'.$website->uuid)
            ->assertNotFound();

        $this->assertDatabaseHas('websites', ['uuid' => $website->uuid]);
    }
}
