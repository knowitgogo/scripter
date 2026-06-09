<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\DTOs\Widget\ListWidgetCatalogQueryDTO;
use App\Models\Widget;
use App\Models\WidgetVersion;
use App\Repositories\Eloquent\EloquentWidgetRepository;
use App\Repositories\Eloquent\EloquentWidgetVersionRepository;
use App\Services\Widget\WidgetService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetServiceTest extends TestCase
{
    use RefreshDatabase;

    private WidgetService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WidgetService(
            new EloquentWidgetRepository,
            new EloquentWidgetVersionRepository,
        );
    }

    #[Test]
    public function it_lists_published_widgets_as_dtos_ordered_by_name(): void
    {
        Widget::factory()->published()->create(['name' => 'Zulu Widget', 'slug' => 'zulu-widget']);
        Widget::factory()->published()->create(['name' => 'Alpha Widget', 'slug' => 'alpha-widget']);
        Widget::factory()->draft()->create(['name' => 'Hidden Widget', 'slug' => 'hidden-widget']);

        $widgets = $this->service->listPublished();

        $this->assertCount(2, $widgets);
        $this->assertSame('Alpha Widget', $widgets[0]->name);
        $this->assertSame('Zulu Widget', $widgets[1]->name);
        $this->assertArrayNotHasKey('id', $widgets[0]->toArray());
    }

    #[Test]
    public function it_filters_published_widgets_by_search_query(): void
    {
        Widget::factory()->published()->create(['name' => 'Feedback Form', 'slug' => 'feedback-form']);
        Widget::factory()->published()->create(['name' => 'Newsletter Signup', 'slug' => 'newsletter-signup']);

        $widgets = $this->service->listPublished(new ListWidgetCatalogQueryDTO(search: 'feedback'));

        $this->assertCount(1, $widgets);
        $this->assertSame('feedback-form', $widgets[0]->slug);
    }

    #[Test]
    public function it_returns_widget_dto_by_uuid_and_slug(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();

        $byUuid = $this->service->getByUuid($widget->uuid);
        $bySlug = $this->service->getBySlug('feedback-form');

        $this->assertSame('feedback-form', $byUuid->slug);
        $this->assertSame('Feedback Form', $bySlug->name);
    }

    #[Test]
    public function it_lists_widget_versions_for_widget(): void
    {
        $widget = Widget::factory()->create();
        WidgetVersion::factory()->for($widget)->release('1.0.0')->create();
        WidgetVersion::factory()->for($widget)->draft()->create(['version' => '1.1.0']);

        $versions = $this->service->listVersionsForWidget($widget->uuid);

        $this->assertCount(2, $versions);
        $this->assertSame($widget->uuid, $versions[0]->widget_uuid);
    }

    #[Test]
    public function it_lists_published_widget_versions_for_widget(): void
    {
        $widget = Widget::factory()->create();
        WidgetVersion::factory()->for($widget)->release('1.0.0')->create();
        WidgetVersion::factory()->for($widget)->draft()->create(['version' => '1.1.0']);

        $versions = $this->service->listPublishedVersionsForWidget($widget->uuid);

        $this->assertCount(1, $versions);
        $this->assertSame('1.0.0', $versions[0]->version);
    }

    #[Test]
    public function it_returns_widget_version_dto_by_uuid(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();
        $version = WidgetVersion::factory()->for($widget)->release('1.2.0')->create();

        $dto = $this->service->getVersionByUuid($version->uuid);

        $this->assertSame('1.2.0', $dto->version);
        $this->assertSame($widget->uuid, $dto->widget_uuid);
    }

    #[Test]
    public function it_throws_when_widget_is_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }

    #[Test]
    public function it_throws_when_widget_version_is_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getVersionByUuid('00000000-0000-0000-0000-000000000000');
    }
}
