<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\DTOs\Widget\InstallWidgetDTO;
use App\DTOs\Widget\UpdateWebsiteWidgetDTO;
use App\Enums\AuditAction;
use App\Enums\WebsiteWidgetStatus;
use App\Exceptions\DomainException;
use App\Models\Role;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteWidget;
use App\Models\Widget;
use App\Models\WidgetVersion;
use App\Repositories\Eloquent\EloquentWebsiteRepository;
use App\Repositories\Eloquent\EloquentWebsiteWidgetRepository;
use App\Repositories\Eloquent\EloquentWidgetVersionRepository;
use App\Services\Audit\AuditDispatcher;
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

        config(['audit.enabled' => true, 'audit.async' => false]);

        $this->service = new WebsiteWidgetService(
            new EloquentWebsiteRepository,
            new EloquentWebsiteWidgetRepository,
            new EloquentWidgetVersionRepository,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_lists_website_widgets_for_user_as_dtos(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        WebsiteWidget::factory()->for($website)->count(2)->create();

        $websiteWidgets = $this->service->listForUser($user);

        $this->assertCount(2, $websiteWidgets);
        $this->assertSame($website->uuid, $websiteWidgets[0]->website_uuid);
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
    public function it_returns_website_widget_for_user(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $websiteWidget = WebsiteWidget::factory()->for($website)->create();

        $dto = $this->service->getForUser($websiteWidget->uuid, $user);

        $this->assertSame($websiteWidget->uuid, $dto->uuid);
    }

    #[Test]
    public function it_installs_published_widget_version_on_owned_website(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $widget = Widget::factory()->published()->create();
        $version = WidgetVersion::factory()->for($widget)->published()->create();

        $dto = $this->service->install(
            new InstallWidgetDTO(
                website_uuid: $website->uuid,
                widget_version_uuid: $version->uuid,
                configuration: ['theme' => 'dark'],
            ),
            $user,
        );

        $this->assertSame($website->uuid, $dto->website_uuid);
        $this->assertSame($version->uuid, $dto->widget_version_uuid);
        $this->assertSame(WebsiteWidgetStatus::Active, $dto->status);
        $this->assertSame('dark', $dto->configuration['theme']);
        $this->assertDatabaseHas('website_widgets', [
            'website_id' => $website->id,
            'widget_version_id' => $version->id,
            'status' => WebsiteWidgetStatus::Active->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_type' => 'website_widget',
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function it_updates_website_widget_for_user(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $websiteWidget = WebsiteWidget::factory()->for($website)->active()->create();

        $dto = $this->service->update(
            $websiteWidget->uuid,
            new UpdateWebsiteWidgetDTO(
                status: WebsiteWidgetStatus::Inactive,
                configuration: ['theme' => 'light'],
            ),
            $user,
        );

        $this->assertSame(WebsiteWidgetStatus::Inactive, $dto->status);
        $this->assertSame('light', $dto->configuration['theme']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_type' => 'website_widget',
            'subject_uuid' => $websiteWidget->uuid,
        ]);
    }

    #[Test]
    public function it_uninstalls_website_widget_for_user(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $websiteWidget = WebsiteWidget::factory()->for($website)->create();

        $this->service->uninstall($websiteWidget->uuid, $user);

        $this->assertDatabaseMissing('website_widgets', ['uuid' => $websiteWidget->uuid]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deleted->value,
            'subject_type' => 'website_widget',
            'subject_uuid' => $websiteWidget->uuid,
        ]);
    }

    #[Test]
    public function it_throws_when_installing_unpublished_widget_version(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $version = WidgetVersion::factory()->draft()->create();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Only published widget versions can be installed.');

        $this->service->install(
            new InstallWidgetDTO(
                website_uuid: $website->uuid,
                widget_version_uuid: $version->uuid,
            ),
            $user,
        );
    }

    #[Test]
    public function it_throws_when_widget_version_is_already_installed(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $user->id]);
        $version = WidgetVersion::factory()->published()->create();
        WebsiteWidget::factory()->for($website)->for($version)->create();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('This widget version is already installed on the website.');

        $this->service->install(
            new InstallWidgetDTO(
                website_uuid: $website->uuid,
                widget_version_uuid: $version->uuid,
            ),
            $user,
        );
    }

    #[Test]
    public function it_throws_when_website_is_not_owned_for_install(): void
    {
        $role = Role::factory()->customer()->create();
        $owner = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $owner->id]);
        $version = WidgetVersion::factory()->published()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->install(
            new InstallWidgetDTO(
                website_uuid: $website->uuid,
                widget_version_uuid: $version->uuid,
            ),
            $otherUser,
        );
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

    #[Test]
    public function it_throws_when_user_does_not_own_website_widget_for_update(): void
    {
        $role = Role::factory()->customer()->create();
        $owner = User::factory()->create(['role_id' => $role->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create(['user_id' => $owner->id]);
        $websiteWidget = WebsiteWidget::factory()->for($website)->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->update(
            $websiteWidget->uuid,
            new UpdateWebsiteWidgetDTO(status: WebsiteWidgetStatus::Inactive),
            $otherUser,
        );
    }
}
