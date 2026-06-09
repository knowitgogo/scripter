<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Website;

use App\DTOs\Website\CreateWebsiteDTO;
use App\DTOs\Website\ListWebsitesQueryDTO;
use App\DTOs\Website\UpdateWebsiteDTO;
use App\Enums\AuditAction;
use App\Enums\WebsiteStatus;
use App\Exceptions\DomainException;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Models\Website;
use App\Repositories\Eloquent\EloquentTagRepository;
use App\Repositories\Eloquent\EloquentWebsiteRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Website\WebsiteService;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebsiteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();

        $this->service = new WebsiteService(
            new EloquentWebsiteRepository,
            new EloquentTagRepository,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_creates_website_for_user(): void
    {
        $user = $this->createUser();

        $dto = $this->service->create(new CreateWebsiteDTO(
            name: 'Acme Site',
            url: 'https://acme.example.com',
        ), $user);

        $this->assertSame('Acme Site', $dto->name);
        $this->assertSame('https://acme.example.com', $dto->url);
        $this->assertSame(WebsiteStatus::Active, $dto->status);
        $this->assertNotEmpty($dto->uuid);

        $this->assertDatabaseHas('websites', [
            'uuid' => $dto->uuid,
            'user_id' => $user->id,
            'name' => 'Acme Site',
            'url' => 'https://acme.example.com',
            'status' => WebsiteStatus::Active->value,
        ]);
    }

    #[Test]
    public function it_throws_when_url_is_already_taken(): void
    {
        $user = $this->createUser();
        Website::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://taken.example.com',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The url has already been taken.');

        $this->service->create(new CreateWebsiteDTO(
            name: 'Another Site',
            url: 'https://taken.example.com',
        ), $user);
    }

    #[Test]
    public function it_records_created_audit_event(): void
    {
        $user = $this->createUser();

        $dto = $this->service->create(new CreateWebsiteDTO(
            name: 'Audit Site',
            url: 'https://audit.example.com',
        ), $user);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_type' => 'website',
            'subject_uuid' => $dto->uuid,
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function it_lists_websites_for_user_as_dtos(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        Website::factory()->create([
            'user_id' => $user->id,
            'name' => 'Beta Site',
        ]);
        Website::factory()->create([
            'user_id' => $user->id,
            'name' => 'Alpha Site',
        ]);
        Website::factory()->create(['user_id' => $otherUser->id]);

        $websites = $this->service->listForUser($user);

        $this->assertCount(2, $websites);
        $this->assertSame('Alpha Site', $websites[0]->name);
        $this->assertSame('Beta Site', $websites[1]->name);
        $this->assertArrayNotHasKey('user_id', $websites[0]->toArray());
    }

    #[Test]
    public function it_filters_websites_by_single_tag(): void
    {
        $user = $this->createUser();
        $marketing = Tag::factory()->marketing()->create();
        $taggedWebsite = Website::factory()->create(['user_id' => $user->id, 'name' => 'Tagged Site']);
        Website::factory()->create(['user_id' => $user->id, 'name' => 'Untagged Site']);
        $taggedWebsite->tags()->attach($marketing);

        $websites = $this->service->listForUser($user, new ListWebsitesQueryDTO(
            tag_uuids: [$marketing->uuid],
        ));

        $this->assertCount(1, $websites);
        $this->assertSame('Tagged Site', $websites[0]->name);
    }

    #[Test]
    public function it_filters_websites_by_multiple_tags_using_and_semantics(): void
    {
        $user = $this->createUser();
        $marketing = Tag::factory()->marketing()->create();
        $ecommerce = Tag::factory()->ecommerce()->create();
        $bothTagsWebsite = Website::factory()->create(['user_id' => $user->id, 'name' => 'Both Tags']);
        $singleTagWebsite = Website::factory()->create(['user_id' => $user->id, 'name' => 'Single Tag']);
        $bothTagsWebsite->tags()->attach([$marketing->id, $ecommerce->id]);
        $singleTagWebsite->tags()->attach($marketing);

        $websites = $this->service->listForUser($user, new ListWebsitesQueryDTO(
            tag_uuids: [$marketing->uuid, $ecommerce->uuid],
        ));

        $this->assertCount(1, $websites);
        $this->assertSame('Both Tags', $websites[0]->name);
    }

    #[Test]
    public function it_returns_website_for_owner(): void
    {
        $user = $this->createUser();
        $website = Website::factory()->create(['user_id' => $user->id]);

        $dto = $this->service->getForUser($website->uuid, $user);

        $this->assertSame($website->uuid, $dto->uuid);
    }

    #[Test]
    public function it_throws_when_website_belongs_to_another_user(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $website = Website::factory()->create(['user_id' => $otherUser->id]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->getForUser($website->uuid, $user);
    }

    #[Test]
    public function it_updates_website_for_owner(): void
    {
        $user = $this->createUser();
        $website = Website::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Name',
            'url' => 'https://old.example.com',
        ]);

        $dto = $this->service->update($website->uuid, new UpdateWebsiteDTO(
            name: 'New Name',
            url: 'https://new.example.com',
        ), $user);

        $this->assertSame('New Name', $dto->name);
        $this->assertSame('https://new.example.com', $dto->url);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_uuid' => $website->uuid,
        ]);
    }

    #[Test]
    public function it_deletes_website_for_owner(): void
    {
        $user = $this->createUser();
        $website = Website::factory()->create(['user_id' => $user->id]);

        $this->service->delete($website->uuid, $user);

        $this->assertDatabaseMissing('websites', ['uuid' => $website->uuid]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deleted->value,
            'subject_uuid' => $website->uuid,
        ]);
    }

    private function createUser(): User
    {
        $role = Role::query()->where('slug', 'customer')->firstOrFail();

        return User::factory()->create(['role_id' => $role->id]);
    }
}
