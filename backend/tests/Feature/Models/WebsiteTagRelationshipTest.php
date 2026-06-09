<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteTagRelationshipTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function website_tags_relationship_uses_website_tags_pivot_table(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->marketing()->create();

        $website->tags()->attach($tag);

        $this->assertDatabaseHas('website_tags', [
            'website_id' => $website->id,
            'tag_id' => $tag->id,
        ]);
    }

    #[Test]
    public function website_tags_relationship_returns_custom_pivot_model(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->marketing()->create();

        $website->tags()->attach($tag);

        $pivot = $website->tags()->first()->pivot;

        $this->assertInstanceOf(WebsiteTag::class, $pivot);
    }

    #[Test]
    public function tag_can_be_shared_across_websites_via_website_tags_pivot(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $tag = Tag::factory()->ecommerce()->create();
        $websites = Website::factory()->count(2)->create(['user_id' => $user->id]);

        foreach ($websites as $website) {
            $website->tags()->attach($tag);
        }

        $this->assertSame(1, Tag::query()->where('slug', 'ecommerce')->count());
        $this->assertDatabaseCount('website_tags', 2);
        $this->assertCount(2, $tag->fresh()->websites);
    }

    #[Test]
    public function website_tags_pivot_records_timestamps(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->marketing()->create();

        $website->tags()->attach($tag);

        $this->assertDatabaseHas('website_tags', [
            'website_id' => $website->id,
            'tag_id' => $tag->id,
        ]);

        $pivot = WebsiteTag::query()
            ->where('website_id', $website->id)
            ->where('tag_id', $tag->id)
            ->first();

        $this->assertNotNull($pivot?->created_at);
        $this->assertNotNull($pivot?->updated_at);
    }
}
