<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Website;

use App\DTOs\Website\SyncWebsiteTagsDTO;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Models\Website;
use App\Repositories\Eloquent\EloquentTagRepository;
use App\Repositories\Eloquent\EloquentWebsiteRepository;
use App\Repositories\Eloquent\EloquentWebsiteTagRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Tag\TagService;
use App\Services\Website\WebsiteTagService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteTagServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebsiteTagService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        $tagService = new TagService(
            new EloquentTagRepository,
            new EloquentWebsiteTagRepository,
            new EloquentWebsiteRepository,
            app(AuditDispatcher::class),
        );

        $this->service = new WebsiteTagService($tagService);
    }

    #[Test]
    public function it_delegates_list_for_website_to_tag_service(): void
    {
        [$user, $website, $tag] = $this->createOwnedWebsiteWithTag();

        $tags = $this->service->listForWebsite($website->uuid, $user);

        $this->assertCount(1, $tags);
        $this->assertSame('marketing', $tags[0]->slug);
    }

    #[Test]
    public function it_delegates_attach_to_tag_service(): void
    {
        [$user, $website] = $this->createOwnedWebsite();
        $tag = Tag::factory()->marketing()->create();

        $result = $this->service->attach($website->uuid, $tag->uuid, $user);

        $this->assertSame($website->uuid, $result->website_uuid);
        $this->assertCount(1, $result->tags);
    }

    #[Test]
    public function it_delegates_detach_to_tag_service(): void
    {
        [$user, $website, $tag] = $this->createOwnedWebsiteWithTag();

        $result = $this->service->detach($website->uuid, $tag->uuid, $user);

        $this->assertCount(0, $result->tags);
    }

    #[Test]
    public function it_delegates_sync_to_tag_service(): void
    {
        [$user, $website] = $this->createOwnedWebsite();
        $marketing = Tag::factory()->marketing()->create();
        $ecommerce = Tag::factory()->ecommerce()->create();
        $website->tags()->attach($marketing);

        $result = $this->service->sync(
            $website->uuid,
            new SyncWebsiteTagsDTO(tag_uuids: [$ecommerce->uuid]),
            $user,
        );

        $this->assertSame('ecommerce', $result->tags[0]->slug);
    }

    #[Test]
    public function it_delegates_ownership_checks_to_tag_service(): void
    {
        $role = Role::factory()->customer()->create();
        $owner = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $owner->id]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->listForWebsite($website->uuid, $otherUser);
    }

    /**
     * @return array{0: User, 1: Website}
     */
    private function createOwnedWebsite(): array
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);

        return [$user, $website];
    }

    /**
     * @return array{0: User, 1: Website, 2: Tag}
     */
    private function createOwnedWebsiteWithTag(): array
    {
        [$user, $website] = $this->createOwnedWebsite();
        $tag = Tag::factory()->marketing()->create();
        $website->tags()->attach($tag);

        return [$user, $website, $tag];
    }
}
