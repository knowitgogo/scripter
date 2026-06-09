<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Website;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWebsites;
use Tests\TestCase;

final class WebsiteAuthorizationEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWebsites, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function website_endpoints_return_401_without_authentication(): void
    {
        $user = $this->createAuthUser();
        $website = $this->createWebsiteFor($user);

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
        $website = $this->createWebsiteFor($user);

        $this->actingAsJwt($user)->getJson('/api/v1/websites')->assertForbidden();
        $this->actingAsJwt($user)->postJson('/api/v1/websites', $this->websitePayload())->assertForbidden();
        $this->actingAsJwt($user)->getJson('/api/v1/websites/'.$website->uuid)->assertForbidden();
        $this->actingAsJwt($user)->putJson('/api/v1/websites/'.$website->uuid, $this->websitePayload())->assertForbidden();
        $this->actingAsJwt($user)->deleteJson('/api/v1/websites/'.$website->uuid)->assertForbidden();
    }
}
