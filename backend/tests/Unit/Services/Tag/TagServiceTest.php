<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Tag;

use App\DTOs\Tag\CreateTagDTO;
use App\DTOs\Tag\UpdateTagDTO;
use App\DTOs\Website\SyncWebsiteTagsDTO;
use App\Enums\AuditAction;
use App\Exceptions\DomainException;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Models\Website;
use App\Repositories\Eloquent\EloquentTagRepository;
use App\Repositories\Eloquent\EloquentWebsiteRepository;
use App\Repositories\Eloquent\EloquentWebsiteTagRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Tag\TagService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    private TagService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        $this->service = new TagService(
            new EloquentTagRepository,
            new EloquentWebsiteTagRepository,
            new EloquentWebsiteRepository,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_lists_tags_as_dtos_ordered_by_name(): void
    {
        Tag::factory()->create(['name' => 'Zulu', 'slug' => 'zulu']);
        Tag::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);

        $tags = $this->service->list();

        $this->assertCount(2, $tags);
        $this->assertSame('Alpha', $tags[0]->name);
        $this->assertSame('Zulu', $tags[1]->name);
        $this->assertArrayNotHasKey('id', $tags[0]->toArray());
    }

    #[Test]
    public function it_returns_tag_dto_by_uuid(): void
    {
        $tag = Tag::factory()->marketing()->create();

        $dto = $this->service->getByUuid($tag->uuid);

        $this->assertSame('marketing', $dto->slug);
    }

    #[Test]
    public function it_returns_tag_dto_by_slug(): void
    {
        Tag::factory()->ecommerce()->create();

        $dto = $this->service->getBySlug('ecommerce');

        $this->assertSame('E-Commerce', $dto->name);
    }

    #[Test]
    public function it_throws_when_tag_is_not_found_by_uuid(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }

    #[Test]
    public function it_lists_tags_for_owned_website_as_dtos(): void
    {
        [$user, $website, $tag] = $this->createOwnedWebsiteWithTag();

        $tags = $this->service->listForWebsite($website->uuid, $user);

        $this->assertCount(1, $tags);
        $this->assertSame('marketing', $tags[0]->slug);
        $this->assertArrayNotHasKey('id', $tags[0]->toArray());
    }

    #[Test]
    public function it_attaches_tag_to_owned_website(): void
    {
        [$user, $website] = $this->createOwnedWebsite();
        $tag = Tag::factory()->marketing()->create();

        $result = $this->service->attach($website->uuid, $tag->uuid, $user);

        $this->assertSame($website->uuid, $result->website_uuid);
        $this->assertCount(1, $result->tags);
        $this->assertSame('marketing', $result->tags[0]->slug);
    }

    #[Test]
    public function it_detaches_tag_from_owned_website(): void
    {
        [$user, $website, $tag] = $this->createOwnedWebsiteWithTag();

        $result = $this->service->detach($website->uuid, $tag->uuid, $user);

        $this->assertSame($website->uuid, $result->website_uuid);
        $this->assertCount(0, $result->tags);
    }

    #[Test]
    public function it_syncs_tags_for_owned_website(): void
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

        $this->assertCount(1, $result->tags);
        $this->assertSame('ecommerce', $result->tags[0]->slug);
    }

    #[Test]
    public function it_throws_when_website_is_not_owned_by_user(): void
    {
        $role = Role::factory()->customer()->create();
        $owner = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $owner->id]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->listForWebsite($website->uuid, $otherUser);
    }

    #[Test]
    public function it_creates_tag_and_records_audit(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $dto = $this->service->create(new CreateTagDTO(
            name: 'Analytics',
            slug: 'analytics',
        ), $user);

        $this->assertSame('analytics', $dto->slug);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_type' => 'tag',
            'subject_uuid' => $dto->uuid,
        ]);
    }

    #[Test]
    public function it_throws_when_create_slug_is_taken(): void
    {
        Tag::factory()->create(['slug' => 'marketing']);
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->expectException(DomainException::class);

        $this->service->create(new CreateTagDTO(
            name: 'Marketing',
            slug: 'marketing',
        ), $user);
    }

    #[Test]
    public function it_updates_tag_and_records_audit(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $tag = Tag::factory()->marketing()->create();

        $dto = $this->service->update($tag->uuid, new UpdateTagDTO(
            name: 'Growth Marketing',
            slug: 'growth-marketing',
        ), $user);

        $this->assertSame('growth-marketing', $dto->slug);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_uuid' => $tag->uuid,
        ]);
    }

    #[Test]
    public function it_deletes_tag_and_records_audit(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $tag = Tag::factory()->marketing()->create();

        $this->service->delete($tag->uuid, $user);

        $this->assertDatabaseMissing('tags', ['uuid' => $tag->uuid]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deleted->value,
            'subject_uuid' => $tag->uuid,
        ]);
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
