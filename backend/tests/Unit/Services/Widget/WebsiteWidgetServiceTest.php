<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\Models\Role;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteWidget;
use App\Repositories\Eloquent\EloquentWebsiteRepository;
use App\Repositories\Eloquent\EloquentWebsiteWidgetRepository;
use App\Services\Widget\WebsiteWidgetService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteWidgetServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebsiteWidgetService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WebsiteWidgetService(
            new EloquentWebsiteRepository,
            new EloquentWebsiteWidgetRepository,
        );
    }

    #[Test]
    public function it_lists_website_widgets_for_website_as_dtos(): void
    {
        $website = Website::factory()->create();
        WebsiteWidget::factory()->for($website)->count(2)->create();

        $websiteWidgets = $this->service->listForWebsite($website->uuid);

        $this->assertCount(2, $websiteWidgets);
        $this->assertSame($website->uuid, $websiteWidgets[0]->website_uuid);
        $this->assertArrayNotHasKey('id', $websiteWidgets[0]->toArray());
    }

    #[Test]
    public function it_returns_website_widget_dto_by_uuid_and_for_website(): void
    {
        $website = Website::factory()->create();
        $websiteWidget = WebsiteWidget::factory()->for($website)->create();

        $byUuid = $this->service->getByUuid($websiteWidget->uuid);
        $forWebsite = $this->service->getByUuidForWebsite($website->uuid, $websiteWidget->uuid);

        $this->assertSame($websiteWidget->uuid, $byUuid->uuid);
        $this->assertSame($website->uuid, $forWebsite->website_uuid);
    }

    #[Test]
    public function it_throws_when_website_widget_is_not_found_by_uuid(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }

    #[Test]
    public function it_throws_when_website_is_not_found_for_listing(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->listForWebsite('00000000-0000-0000-0000-000000000000');
    }

    #[Test]
    public function it_throws_when_website_widget_does_not_belong_to_website(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $otherWebsite = Website::factory()->create(['user_id' => $user->id]);
        $websiteWidget = WebsiteWidget::factory()->for($otherWebsite)->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuidForWebsite($website->uuid, $websiteWidget->uuid);
    }
}
