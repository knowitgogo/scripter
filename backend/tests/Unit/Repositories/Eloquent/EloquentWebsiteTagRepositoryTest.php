<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Models\Website;
use App\Repositories\Contracts\WebsiteTagRepositoryInterface;
use App\Repositories\Eloquent\EloquentWebsiteTagRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentWebsiteTagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentWebsiteTagRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentWebsiteTagRepository;
    }

    #[Test]
    public function it_implements_website_tag_repository_contract(): void
    {
        $this->assertInstanceOf(WebsiteTagRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function it_attaches_and_lists_tags_for_website(): void
    {
        [$website, $tag] = $this->createWebsiteAndTag();

        $this->repository->attach($website->id, $tag->id);

        $tags = $this->repository->listTagsForWebsite($website->id);

        $this->assertTrue($this->repository->isAttached($website->id, $tag->id));
        $this->assertCount(1, $tags);
        $this->assertTrue($tag->is($tags->first()));
    }

    #[Test]
    public function attach_is_idempotent_for_existing_pairs(): void
    {
        [$website, $tag] = $this->createWebsiteAndTag();

        $this->repository->attach($website->id, $tag->id);
        $this->repository->attach($website->id, $tag->id);

        $this->assertDatabaseCount('website_tags', 1);
    }

    #[Test]
    public function it_detaches_tags_from_website(): void
    {
        [$website, $tag] = $this->createWebsiteAndTag();
        $this->repository->attach($website->id, $tag->id);

        $this->repository->detach($website->id, $tag->id);

        $this->assertFalse($this->repository->isAttached($website->id, $tag->id));
        $this->assertCount(0, $this->repository->listTagsForWebsite($website->id));
    }

    #[Test]
    public function it_syncs_website_tags(): void
    {
        [$website, $firstTag, $secondTag] = $this->createWebsiteWithTwoTags();
        $this->repository->attach($website->id, $firstTag->id);

        $result = $this->repository->sync($website->id, [$secondTag->id]);

        $this->assertSame([$secondTag->id], $result['attached']);
        $this->assertSame([$firstTag->id], $result['detached']);
        $this->assertSame([], $result['updated']);
        $this->assertTrue($this->repository->isAttached($website->id, $secondTag->id));
        $this->assertFalse($this->repository->isAttached($website->id, $firstTag->id));
    }

    #[Test]
    public function it_throws_when_website_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->listTagsForWebsite(99999);
    }

    /**
     * @return array{0: Website, 1: Tag}
     */
    private function createWebsiteAndTag(): array
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->marketing()->create();

        return [$website, $tag];
    }

    /**
     * @return array{0: Website, 1: Tag, 2: Tag}
     */
    private function createWebsiteWithTwoTags(): array
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $firstTag = Tag::factory()->marketing()->create();
        $secondTag = Tag::factory()->ecommerce()->create();

        return [$website, $firstTag, $secondTag];
    }
}
