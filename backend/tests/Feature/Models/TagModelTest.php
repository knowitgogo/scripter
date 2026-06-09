<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Models\Website;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TagModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function tag_receives_uuid_on_creation(): void
    {
        $tag = Tag::factory()->create();

        $this->assertNotEmpty($tag->uuid);
        $this->assertTrue(UuidGenerator::isValid($tag->uuid));
    }

    #[Test]
    public function tag_internal_id_is_not_exposed_in_array(): void
    {
        $tag = Tag::factory()->create();

        $array = $tag->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function tag_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new Tag)->getRouteKeyName());
    }

    #[Test]
    public function tag_can_be_attached_to_multiple_websites(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $tag = Tag::factory()->marketing()->create();
        $firstWebsite = Website::factory()->create(['user_id' => $user->id]);
        $secondWebsite = Website::factory()->create(['user_id' => $user->id]);

        $firstWebsite->tags()->attach($tag);
        $secondWebsite->tags()->attach($tag);

        $this->assertCount(2, $tag->fresh()->websites);
        $this->assertTrue($tag->is($firstWebsite->fresh()->tags->first()));
        $this->assertTrue($tag->is($secondWebsite->fresh()->tags->first()));
    }

    #[Test]
    public function website_can_share_the_same_tag_instance(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $tag = Tag::factory()->ecommerce()->create();
        $websites = Website::factory()->count(2)->create(['user_id' => $user->id]);

        foreach ($websites as $website) {
            $website->tags()->attach($tag);
        }

        $this->assertSame(1, Tag::query()->where('slug', 'ecommerce')->count());
        $this->assertDatabaseCount('website_tag', 2);
    }
}
