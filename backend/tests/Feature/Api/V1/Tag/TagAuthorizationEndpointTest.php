<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tag;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithTags;
use Tests\TestCase;

final class TagAuthorizationEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithTags, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function tag_endpoints_return_401_without_authentication(): void
    {
        $user = $this->createAuthUser();
        $tag = $this->createTagFor($user);

        $this->getJson('/api/v1/tags')->assertUnauthorized();
        $this->postJson('/api/v1/tags', [])->assertUnauthorized();
        $this->getJson('/api/v1/tags/'.$tag->uuid)->assertUnauthorized();
        $this->putJson('/api/v1/tags/'.$tag->uuid, [])->assertUnauthorized();
        $this->deleteJson('/api/v1/tags/'.$tag->uuid)->assertUnauthorized();
    }

    #[Test]
    public function tag_endpoints_return_403_without_required_permission(): void
    {
        config(['permissions.roles.customer' => []]);

        $user = $this->createAuthUser();
        $tag = $this->createTagFor($user);

        $this->actingAsJwt($user)->getJson('/api/v1/tags')->assertForbidden();
        $this->actingAsJwt($user)->postJson('/api/v1/tags', $this->tagPayload())->assertForbidden();
        $this->actingAsJwt($user)->getJson('/api/v1/tags/'.$tag->uuid)->assertForbidden();
        $this->actingAsJwt($user)->putJson('/api/v1/tags/'.$tag->uuid, $this->tagPayload())->assertForbidden();
        $this->actingAsJwt($user)->deleteJson('/api/v1/tags/'.$tag->uuid)->assertForbidden();
    }
}
