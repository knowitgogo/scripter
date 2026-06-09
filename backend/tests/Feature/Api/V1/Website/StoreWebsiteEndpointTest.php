<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Website;

use App\Enums\AuditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWebsites;
use Tests\TestCase;

final class StoreWebsiteEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWebsites, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function store_endpoint_creates_website(): void
    {
        $user = $this->createAuthUser();

        $response = $this->actingAsJwt($user)->postJson(
            '/api/v1/websites',
            $this->websitePayload([
                'name' => 'Acme Site',
                'url' => 'https://acme.example.com',
            ]),
        );

        $response->assertCreated();
        $this->assertSuccessfulApiEnvelope($response, 201);
        $response->assertJson([
            'data' => [
                'name' => 'Acme Site',
                'url' => 'https://acme.example.com',
                'status' => 'active',
            ],
        ]);
        $response->assertJsonStructure([
            'data' => ['uuid', 'name', 'url', 'status', 'created_at', 'updated_at'],
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
    public function store_endpoint_returns_422_for_duplicate_url(): void
    {
        $user = $this->createAuthUser();
        $this->createWebsiteFor($user, ['url' => 'https://taken.example.com']);

        $this->actingAsJwt($user)
            ->postJson('/api/v1/websites', $this->websitePayload([
                'url' => 'https://taken.example.com',
            ]))
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
            ->postJson('/api/v1/websites', [])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonStructure(['errors']);
    }
}
