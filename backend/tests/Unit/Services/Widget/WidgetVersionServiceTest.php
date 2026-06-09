<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\Enums\AuditAction;
use App\Enums\WidgetVersionStatus;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Models\Widget;
use App\Models\WidgetVersion;
use App\Repositories\Eloquent\EloquentWidgetRepository;
use App\Repositories\Eloquent\EloquentWidgetVersionRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Widget\WidgetVersionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetVersionServiceTest extends TestCase
{
    use RefreshDatabase;

    private WidgetVersionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        $this->service = new WidgetVersionService(
            new EloquentWidgetRepository,
            new EloquentWidgetVersionRepository,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_lists_versions_for_widget_as_dtos(): void
    {
        $widget = Widget::factory()->create();
        WidgetVersion::factory()->for($widget)->release('1.0.0')->create();
        WidgetVersion::factory()->for($widget)->draft()->create(['version' => '1.1.0']);

        $versions = $this->service->listForWidget($widget->uuid);

        $this->assertCount(2, $versions);
        $this->assertSame($widget->uuid, $versions[0]->widget_uuid);
        $this->assertArrayNotHasKey('widget_id', $versions[0]->toArray());
    }

    #[Test]
    public function it_lists_published_versions_for_widget(): void
    {
        $widget = Widget::factory()->create();
        WidgetVersion::factory()->for($widget)->release('1.0.0')->create();
        WidgetVersion::factory()->for($widget)->draft()->create(['version' => '1.1.0']);

        $versions = $this->service->listPublishedForWidget($widget->uuid);

        $this->assertCount(1, $versions);
        $this->assertSame('1.0.0', $versions[0]->version);
        $this->assertSame('published', $versions[0]->toArray()['status']);
    }

    #[Test]
    public function it_returns_widget_version_dto_by_uuid(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();
        $version = WidgetVersion::factory()->for($widget)->release('1.2.0')->create();

        $dto = $this->service->getByUuid($version->uuid);

        $this->assertSame('1.2.0', $dto->version);
        $this->assertSame($widget->uuid, $dto->widget_uuid);
    }

    #[Test]
    public function it_publishes_draft_widget_version(): void
    {
        $user = User::factory()->create();
        $widget = Widget::factory()->create();
        $version = WidgetVersion::factory()->for($widget)->draft()->create([
            'version' => '1.0.0',
            'asset_manifest_url' => 'https://cdn.example.com/manifest.json',
        ]);

        $dto = $this->service->publish($version->uuid, $user);

        $this->assertSame(WidgetVersionStatus::Published, $dto->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Published->value,
            'subject_uuid' => $version->uuid,
        ]);
    }

    #[Test]
    public function it_deprecates_previous_published_version_when_publishing_new_one(): void
    {
        $user = User::factory()->create();
        $widget = Widget::factory()->create();
        $existing = WidgetVersion::factory()->for($widget)->release('1.0.0')->create();
        $next = WidgetVersion::factory()->for($widget)->draft()->create([
            'version' => '1.1.0',
            'asset_manifest_url' => 'https://cdn.example.com/manifest-1.1.0.json',
        ]);

        $this->service->publish($next->uuid, $user);

        $this->assertDatabaseHas('widget_versions', [
            'uuid' => $existing->uuid,
            'status' => WidgetVersionStatus::Deprecated->value,
        ]);
        $this->assertDatabaseHas('widget_versions', [
            'uuid' => $next->uuid,
            'status' => WidgetVersionStatus::Published->value,
        ]);
    }

    #[Test]
    public function it_deprecates_published_widget_version(): void
    {
        $user = User::factory()->create();
        $widget = Widget::factory()->create();
        $version = WidgetVersion::factory()->for($widget)->release('1.0.0')->create();

        $dto = $this->service->deprecate($version->uuid, $user);

        $this->assertSame(WidgetVersionStatus::Deprecated, $dto->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deprecated->value,
            'subject_uuid' => $version->uuid,
        ]);
    }

    #[Test]
    public function it_rejects_publish_without_asset_manifest_url(): void
    {
        $widget = Widget::factory()->create();
        $version = WidgetVersion::factory()->for($widget)->draft()->create([
            'version' => '1.0.0',
            'asset_manifest_url' => null,
        ]);
        $user = User::factory()->create();

        $this->expectException(DomainException::class);

        $this->service->publish($version->uuid, $user);
    }

    #[Test]
    public function it_rejects_deprecate_for_draft_widget_version(): void
    {
        $widget = Widget::factory()->create();
        $version = WidgetVersion::factory()->for($widget)->draft()->create([
            'version' => '1.0.0',
            'asset_manifest_url' => 'https://cdn.example.com/manifest.json',
        ]);
        $user = User::factory()->create();

        $this->expectException(DomainException::class);

        $this->service->deprecate($version->uuid, $user);
    }

    #[Test]
    public function it_throws_when_widget_is_not_found_for_version_listing(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->listForWidget('00000000-0000-0000-0000-000000000000');
    }

    #[Test]
    public function it_throws_when_widget_version_is_not_found_by_uuid(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }
}
