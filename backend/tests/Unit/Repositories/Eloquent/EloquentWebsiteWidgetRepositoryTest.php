<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteWidget;
use App\Models\WidgetVersion;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Contracts\WebsiteWidgetRepositoryInterface;
use App\Repositories\Eloquent\EloquentWebsiteWidgetRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentWebsiteWidgetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentWebsiteWidgetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentWebsiteWidgetRepository;
    }

    #[Test]
    public function it_implements_website_widget_and_uuid_repository_contracts(): void
    {
        $this->assertInstanceOf(WebsiteWidgetRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function it_finds_website_widget_by_uuid_and_website(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $otherWebsite = Website::factory()->create(['user_id' => $user->id]);
        $websiteWidget = WebsiteWidget::factory()->for($website)->create();

        $this->assertTrue($websiteWidget->is($this->repository->findByUuid($websiteWidget->uuid)));
        $this->assertTrue($websiteWidget->is($this->repository->findByUuidForWebsite($website->id, $websiteWidget->uuid)));
        $this->assertNull($this->repository->findByUuidForWebsite($otherWebsite->id, $websiteWidget->uuid));
        $this->assertNull($this->repository->findByUuidForWebsite($website->id, 'not-a-uuid'));
    }

    #[Test]
    public function it_finds_website_widget_by_website_and_widget_version(): void
    {
        $website = Website::factory()->create();
        $version = WidgetVersion::factory()->published()->create();
        $websiteWidget = WebsiteWidget::factory()->for($website)->for($version)->create();

        $this->assertTrue($websiteWidget->is($this->repository->findByWebsiteAndWidgetVersion($website->id, $version->id)));
        $this->assertNull($this->repository->findByWebsiteAndWidgetVersion($website->id, 99999));
    }

    #[Test]
    public function it_finds_website_widget_by_uuid_for_user(): void
    {
        $role = Role::factory()->customer()->create();
        $owner = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $owner->id]);
        $websiteWidget = WebsiteWidget::factory()->for($website)->create();

        $this->assertTrue($websiteWidget->is($this->repository->findByUuidForUser($websiteWidget->uuid, $owner->id)));
        $this->assertNull($this->repository->findByUuidForUser($websiteWidget->uuid, $otherUser->id));
        $this->assertNull($this->repository->findByUuidForUser('not-a-uuid', $owner->id));
    }

    #[Test]
    public function it_throws_when_website_widget_is_not_found_for_user(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->expectException(ModelNotFoundException::class);

        $this->repository->findByUuidForUserOrFail('00000000-0000-0000-0000-000000000000', $user->id);
    }

    #[Test]
    public function it_lists_website_widgets_for_website_ordered_by_created_at_desc(): void
    {
        $website = Website::factory()->create();
        $older = WebsiteWidget::factory()->for($website)->create(['created_at' => now()->subDay()]);
        $newer = WebsiteWidget::factory()->for($website)->create(['created_at' => now()]);

        $websiteWidgets = $this->repository->listForWebsite($website->id);

        $this->assertCount(2, $websiteWidgets);
        $this->assertTrue($newer->is($websiteWidgets->first()));
        $this->assertTrue($older->is($websiteWidgets->last()));
    }

    #[Test]
    public function it_lists_website_widgets_for_user_across_owned_websites(): void
    {
        $role = Role::factory()->customer()->create();
        $owner = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);
        $ownedWebsite = Website::factory()->create(['user_id' => $owner->id]);
        $otherWebsite = Website::factory()->create(['user_id' => $otherUser->id]);
        $ownedWidget = WebsiteWidget::factory()->for($ownedWebsite)->create();
        WebsiteWidget::factory()->for($otherWebsite)->create();

        $websiteWidgets = $this->repository->listForUser($owner->id);

        $this->assertCount(1, $websiteWidgets);
        $this->assertTrue($ownedWidget->is($websiteWidgets->first()));
    }

    #[Test]
    public function it_creates_updates_and_deletes_website_widgets(): void
    {
        $website = Website::factory()->create();
        $version = WidgetVersion::factory()->published()->create();

        /** @var WebsiteWidget $websiteWidget */
        $websiteWidget = $this->repository->create([
            'website_id' => $website->id,
            'widget_version_id' => $version->id,
            'configuration_json' => ['theme' => 'light'],
        ]);

        $this->assertNotEmpty($websiteWidget->uuid);
        $this->assertDatabaseHas('website_widgets', ['uuid' => $websiteWidget->uuid]);

        $this->repository->update($websiteWidget, [
            'configuration_json' => ['theme' => 'dark'],
        ]);
        $websiteWidget->refresh();

        $this->assertSame('dark', $websiteWidget->configuration_json['theme']);

        $this->assertTrue($this->repository->delete($websiteWidget));
        $this->assertDatabaseMissing('website_widgets', ['uuid' => $websiteWidget->uuid]);
    }
}
