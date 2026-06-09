<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tag;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithTags;
use Tests\TestCase;

final class ListTagsEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithTags, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function list_endpoint_returns_tags_ordered_by_name(): void
    {
        $user = $this->createAuthUser();
        $this->createTagFor($user, ['name' => 'Zulu', 'slug' => 'zulu']);
        $this->createTagFor($user, ['name' => 'Alpha', 'slug' => 'alpha']);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/tags');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'name', 'slug', 'created_at', 'updated_at'],
            ],
        ]);
        $this->assertSame('Alpha', $response->json('data.0.name'));
        $this->assertSame('Zulu', $response->json('data.1.name'));
    }

    #[Test]
    public function list_endpoint_returns_empty_collection_when_no_tags_exist(): void
    {
        $user = $this->createAuthUser();

        $response = $this->actingAsJwt($user)->getJson('/api/v1/tags');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(0, 'data');
    }
}
