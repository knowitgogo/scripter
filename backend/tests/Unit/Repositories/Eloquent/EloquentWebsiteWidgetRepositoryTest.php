<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteWidget;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Contracts\WebsiteWidgetRepositoryInterface;
use App\Repositories\Eloquent\EloquentWebsiteWidgetRepository;
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
}
