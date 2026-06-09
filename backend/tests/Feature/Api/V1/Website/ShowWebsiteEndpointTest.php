<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Website;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWebsites;
use Tests\TestCase;

final class ShowWebsiteEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWebsites, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function show_endpoint_returns_owned_website(): void
    {
        $user = $this->createAuthUser();
        $website = $this->createWebsiteFor($user, [
            'name' => 'Acme Site',
            'url' => 'https://acme.example.com',
        ]);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/websites/'.$website->uuid);

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJson([
            'data' => [
                'uuid' => $website->uuid,
                'name' => 'Acme Site',
                'url' => 'https://acme.example.com',
                'status' => 'active',
            ],
        ]);
        $response->assertJsonStructure([
            'data' => ['uuid', 'name', 'url', 'status', 'created_at', 'updated_at'],
        ]);
    }

    #[Test]
    public function show_endpoint_returns_404_for_other_users_website(): void
    {
        $user = $this->createAuthUser();
        $otherUser = $this->createAuthUser();
        $website = $this->createWebsiteFor($otherUser);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/websites/'.$website->uuid)
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => [],
            ]);
    }
}
