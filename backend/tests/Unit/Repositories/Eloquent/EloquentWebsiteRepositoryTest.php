<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Models\Website;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use App\Repositories\Eloquent\EloquentWebsiteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentWebsiteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_website_and_uuid_repository_contracts(): void
    {
        $repository = new EloquentWebsiteRepository;

        $this->assertInstanceOf(WebsiteRepositoryInterface::class, $repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_finds_website_by_uuid(): void
    {
        $website = Website::factory()->create();
        $repository = new EloquentWebsiteRepository;

        $found = $repository->findByUuid($website->uuid);

        $this->assertTrue($website->is($found));
    }

    #[Test]
    public function it_finds_website_by_url(): void
    {
        $website = Website::factory()->create(['url' => 'https://find-me.example.com']);
        $repository = new EloquentWebsiteRepository;

        $found = $repository->findByUrl('https://find-me.example.com');

        $this->assertTrue($website->is($found));
        $this->assertNull($repository->findByUrl('https://missing.example.com'));
    }

    #[Test]
    public function it_lists_websites_for_user_ordered_by_name(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);

        $beta = Website::factory()->create([
            'user_id' => $user->id,
            'name' => 'Beta Site',
        ]);
        $alpha = Website::factory()->create([
            'user_id' => $user->id,
            'name' => 'Alpha Site',
        ]);
        Website::factory()->create(['user_id' => $otherUser->id]);

        $repository = new EloquentWebsiteRepository;
        $websites = $repository->listForUser($user->id);

        $this->assertCount(2, $websites);
        $this->assertTrue($alpha->is($websites->first()));
        $this->assertTrue($beta->is($websites->last()));
    }

    #[Test]
    public function it_filters_websites_for_user_by_tag_ids(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $marketing = Tag::factory()->marketing()->create();
        $ecommerce = Tag::factory()->ecommerce()->create();
        $taggedWebsite = Website::factory()->create(['user_id' => $user->id, 'name' => 'Tagged Site']);
        $otherWebsite = Website::factory()->create(['user_id' => $user->id, 'name' => 'Other Site']);
        $taggedWebsite->tags()->attach($marketing);
        $otherWebsite->tags()->attach([$marketing, $ecommerce]);

        $repository = new EloquentWebsiteRepository;

        $singleTagResults = $repository->listForUser($user->id, [$marketing->id]);
        $bothTagResults = $repository->listForUser($user->id, [$marketing->id, $ecommerce->id]);

        $this->assertCount(2, $singleTagResults);
        $this->assertCount(1, $bothTagResults);
        $this->assertTrue($otherWebsite->is($bothTagResults->first()));
    }

    #[Test]
    public function it_finds_website_by_uuid_scoped_to_user(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);

        $repository = new EloquentWebsiteRepository;

        $found = $repository->findByUuidForUser($website->uuid, $user->id);

        $this->assertTrue($website->is($found));
        $this->assertNull($repository->findByUuidForUser($website->uuid, $otherUser->id));
        $this->assertNull($repository->findByUuidForUser('not-a-uuid', $user->id));
    }

    #[Test]
    public function it_creates_and_updates_website(): void
    {
        $user = User::factory()->create();
        $repository = new EloquentWebsiteRepository;

        /** @var Website $website */
        $website = $repository->create([
            'user_id' => $user->id,
            'name' => 'New Site',
            'url' => 'https://new.example.com',
            'status' => 'active',
        ]);

        $this->assertSame('New Site', $website->name);

        $repository->update($website, ['name' => 'Renamed Site']);

        $this->assertSame('Renamed Site', $website->fresh()->name);
    }
}
