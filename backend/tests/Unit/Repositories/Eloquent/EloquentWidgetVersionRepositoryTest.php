<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Enums\WidgetVersionStatus;
use App\Models\Widget;
use App\Models\WidgetVersion;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Contracts\WidgetVersionRepositoryInterface;
use App\Repositories\Eloquent\EloquentWidgetVersionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentWidgetVersionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_widget_version_and_uuid_repository_contracts(): void
    {
        $repository = new EloquentWidgetVersionRepository;

        $this->assertInstanceOf(WidgetVersionRepositoryInterface::class, $repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_finds_widget_version_by_uuid_and_widget_version_pair(): void
    {
        $widget = Widget::factory()->create();
        $version = WidgetVersion::factory()->for($widget)->create(['version' => '2.0.0']);
        $repository = new EloquentWidgetVersionRepository;

        $this->assertTrue($version->is($repository->findByUuid($version->uuid)));
        $this->assertTrue($version->is($repository->findByWidgetAndVersion($widget->id, '2.0.0')));
        $this->assertNull($repository->findByWidgetAndVersion($widget->id, '9.9.9'));
    }

    #[Test]
    public function it_lists_versions_for_widget_ordered_by_created_at_desc(): void
    {
        $widget = Widget::factory()->create();
        $older = WidgetVersion::factory()->for($widget)->create([
            'version' => '1.0.0',
            'created_at' => now()->subDay(),
        ]);
        $newer = WidgetVersion::factory()->for($widget)->create([
            'version' => '1.1.0',
            'created_at' => now(),
        ]);
        WidgetVersion::factory()->create(['version' => '9.9.9']);

        $repository = new EloquentWidgetVersionRepository;
        $versions = $repository->listForWidget($widget->id);

        $this->assertCount(2, $versions);
        $this->assertTrue($newer->is($versions->first()));
        $this->assertTrue($older->is($versions->last()));
    }

    #[Test]
    public function it_lists_published_versions_for_widget(): void
    {
        $widget = Widget::factory()->create();
        WidgetVersion::factory()->for($widget)->published()->create(['version' => '1.0.0']);
        WidgetVersion::factory()->for($widget)->draft()->create(['version' => '1.1.0']);

        $repository = new EloquentWidgetVersionRepository;

        $this->assertCount(1, $repository->listPublishedForWidget($widget->id));
        $this->assertSame('1.0.0', $repository->listPublishedForWidget($widget->id)->first()->version);
    }

    #[Test]
    public function it_finds_latest_published_version_for_widget(): void
    {
        $widget = Widget::factory()->create();
        WidgetVersion::factory()->for($widget)->published()->create([
            'version' => '1.0.0',
            'created_at' => now()->subDay(),
        ]);
        $latest = WidgetVersion::factory()->for($widget)->published()->create([
            'version' => '1.1.0',
            'created_at' => now(),
        ]);

        $repository = new EloquentWidgetVersionRepository;
        $published = $repository->findPublishedForWidget($widget->id);

        $this->assertNotNull($published);
        $this->assertTrue($latest->is($published));
    }

    #[Test]
    public function it_lists_versions_by_status(): void
    {
        WidgetVersion::factory()->deprecated()->create(['version' => '1.0.0']);
        WidgetVersion::factory()->draft()->create(['version' => '2.0.0']);

        $repository = new EloquentWidgetVersionRepository;

        $this->assertCount(1, $repository->listByStatus(WidgetVersionStatus::Deprecated));
        $this->assertCount(1, $repository->listByStatus(WidgetVersionStatus::Draft));
    }
}
