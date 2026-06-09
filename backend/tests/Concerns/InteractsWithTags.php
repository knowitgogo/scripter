<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Tag;
use App\Models\User;

/**
 * Tag API helpers for feature and integration tests.
 */
trait InteractsWithTags
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, string>
     */
    protected function tagPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Marketing',
            'slug' => 'marketing-'.fake()->unique()->numerify('####'),
        ], $overrides);
    }

    protected function createTagFor(User $user, array $attributes = []): Tag
    {
        unset($user);

        return Tag::factory()->create($attributes);
    }
}
