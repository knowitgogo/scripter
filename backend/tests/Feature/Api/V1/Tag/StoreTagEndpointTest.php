<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tag;

use App\Enums\AuditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithTags;
use Tests\TestCase;

final class StoreTagEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithTags, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function store_endpoint_creates_tag(): void
    {
        $user = $this->createAuthUser();

        $response = $this->actingAsJwt($user)->postJson(
            '/api/v1/tags',
            $this->tagPayload([
                'name' => 'Marketing',
                'slug' => 'marketing',
            ]),
        );

        $response->assertCreated();
        $this->assertSuccessfulApiEnvelope($response, 201);
        $response->assertJson([
            'data' => [
                'name' => 'Marketing',
                'slug' => 'marketing',
            ],
        ]);
        $response->assertJsonStructure([
            'data' => ['uuid', 'name', 'slug', 'created_at', 'updated_at'],
        ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'Marketing',
            'slug' => 'marketing',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_type' => 'tag',
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function store_endpoint_returns_422_for_duplicate_slug(): void
    {
        $user = $this->createAuthUser();
        $this->createTagFor($user, ['slug' => 'marketing']);

        $this->actingAsJwt($user)
            ->postJson('/api/v1/tags', $this->tagPayload(['slug' => 'marketing']))
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }

    #[Test]
    public function store_endpoint_returns_422_for_missing_fields(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->postJson('/api/v1/tags', [])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonStructure(['errors']);
    }
}
