<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tag;

use App\Enums\AuditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithTags;
use Tests\TestCase;

final class UpdateTagEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithTags, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function update_endpoint_updates_tag(): void
    {
        $user = $this->createAuthUser();
        $tag = $this->createTagFor($user, ['name' => 'Marketing', 'slug' => 'marketing']);

        $response = $this->actingAsJwt($user)->putJson('/api/v1/tags/'.$tag->uuid, [
            'name' => 'Growth Marketing',
            'slug' => 'growth-marketing',
        ]);

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJson([
            'data' => [
                'uuid' => $tag->uuid,
                'name' => 'Growth Marketing',
                'slug' => 'growth-marketing',
            ],
        ]);

        $this->assertDatabaseHas('tags', [
            'uuid' => $tag->uuid,
            'name' => 'Growth Marketing',
            'slug' => 'growth-marketing',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_uuid' => $tag->uuid,
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function update_endpoint_returns_422_for_duplicate_slug(): void
    {
        $user = $this->createAuthUser();
        $tag = $this->createTagFor($user, ['slug' => 'marketing']);
        $this->createTagFor($user, ['slug' => 'ecommerce']);

        $this->actingAsJwt($user)
            ->putJson('/api/v1/tags/'.$tag->uuid, [
                'name' => 'Marketing',
                'slug' => 'ecommerce',
            ])
            ->assertUnprocessable();
    }

    #[Test]
    public function update_endpoint_returns_404_for_unknown_uuid(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->putJson('/api/v1/tags/00000000-0000-0000-0000-000000000000', [
                'name' => 'Marketing',
                'slug' => 'marketing',
            ])
            ->assertNotFound();
    }
}
