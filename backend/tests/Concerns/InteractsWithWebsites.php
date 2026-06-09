<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\User;
use App\Models\Website;

/**
 * Website API helpers for feature and integration tests.
 */
trait InteractsWithWebsites
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, string>
     */
    protected function websitePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Site',
            'url' => 'https://'.fake()->unique()->domainName(),
        ], $overrides);
    }

    protected function createWebsiteFor(User $user, array $attributes = []): Website
    {
        return Website::factory()->create(array_merge([
            'user_id' => $user->id,
        ], $attributes));
    }
}
