<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tag;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithTags;
use Tests\TestCase;

final class ShowTagEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithTags, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function show_endpoint_returns_tag_by_uuid(): void
    {
        $user = $this->createAuthUser();
        $tag = $this->createTagFor($user, ['name' => 'Marketing', 'slug' => 'marketing']);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/tags/'.$tag->uuid);

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJson([
            'data' => [
                'uuid' => $tag->uuid,
                'name' => 'Marketing',
                'slug' => 'marketing',
            ],
        ]);
    }

    #[Test]
    public function show_endpoint_returns_404_for_unknown_uuid(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->getJson('/api/v1/tags/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }
}
