<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Website;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithTags;
use Tests\Concerns\InteractsWithWebsites;
use Tests\TestCase;

final class ListWebsitesEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithTags, InteractsWithWebsites, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function list_endpoint_returns_owned_websites_ordered_by_name(): void
    {
        $user = $this->createAuthUser();
        $otherUser = $this->createAuthUser();
        $this->createWebsiteFor($user, ['name' => 'Beta Site']);
        $this->createWebsiteFor($user, ['name' => 'Alpha Site']);
        $this->createWebsiteFor($otherUser);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/websites');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'name', 'url', 'status', 'created_at', 'updated_at'],
            ],
        ]);
        $this->assertSame('Alpha Site', $response->json('data.0.name'));
        $this->assertSame('Beta Site', $response->json('data.1.name'));
        $this->assertArrayNotHasKey('user_id', $response->json('data.0'));
    }

    #[Test]
    public function list_endpoint_returns_empty_collection_when_user_has_no_websites(): void
    {
        $user = $this->createAuthUser();

        $response = $this->actingAsJwt($user)->getJson('/api/v1/websites');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(0, 'data');
    }

    #[Test]
    public function list_endpoint_filters_websites_by_tag_uuid(): void
    {
        $user = $this->createAuthUser();
        $marketing = $this->createTagFor($user, ['slug' => 'marketing-filter']);
        $taggedWebsite = $this->createWebsiteFor($user, ['name' => 'Tagged Site']);
        $this->createWebsiteFor($user, ['name' => 'Untagged Site']);
        $taggedWebsite->tags()->attach($marketing);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/websites?'.http_build_query([
            'tag_uuids' => [$marketing->uuid],
        ]));

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Tagged Site');
    }

    #[Test]
    public function list_endpoint_requires_existing_tag_uuids(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->getJson('/api/v1/websites?'.http_build_query([
                'tag_uuids' => ['00000000-0000-0000-0000-000000000000'],
            ]))
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }
}
