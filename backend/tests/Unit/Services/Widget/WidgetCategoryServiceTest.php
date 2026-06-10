<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\DTOs\Widget\SyncWidgetCategoriesDTO;
use App\Models\Widget;
use App\Models\WidgetCategory;
use App\Repositories\Eloquent\EloquentWidgetCategoryRepository;
use App\Repositories\Eloquent\EloquentWidgetCategoryWidgetRepository;
use App\Repositories\Eloquent\EloquentWidgetRepository;
use App\Services\Widget\WidgetCategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetCategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private WidgetCategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WidgetCategoryService(
            new EloquentWidgetCategoryRepository,
            new EloquentWidgetCategoryWidgetRepository,
            new EloquentWidgetRepository,
        );
    }

    #[Test]
    public function it_lists_widget_categories_as_dtos_ordered_by_name(): void
    {
        WidgetCategory::factory()->create(['name' => 'Zulu', 'slug' => 'zulu']);
        WidgetCategory::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);

        $categories = $this->service->list();

        $this->assertCount(2, $categories);
        $this->assertSame('Alpha', $categories[0]->name);
        $this->assertSame('Zulu', $categories[1]->name);
        $this->assertArrayNotHasKey('id', $categories[0]->toArray());
    }

    #[Test]
    public function it_returns_widget_category_dto_by_uuid_and_slug(): void
    {
        $category = WidgetCategory::factory()->feedback()->create();

        $byUuid = $this->service->getByUuid($category->uuid);
        $bySlug = $this->service->getBySlug('feedback');

        $this->assertSame('feedback', $byUuid->slug);
        $this->assertSame('Feedback', $bySlug->name);
    }

    #[Test]
    public function it_lists_categories_for_widget(): void
    {
        $widget = Widget::factory()->create();
        $category = WidgetCategory::factory()->feedback()->create();
        $widget->categories()->attach($category->id);

        $categories = $this->service->listForWidget($widget->uuid);

        $this->assertCount(1, $categories);
        $this->assertSame('feedback', $categories[0]->slug);
    }

    #[Test]
    public function it_attaches_category_to_widget(): void
    {
        $widget = Widget::factory()->create();
        $category = WidgetCategory::factory()->feedback()->create();

        $result = $this->service->attach($widget->uuid, $category->uuid);

        $this->assertSame($widget->uuid, $result->widget_uuid);
        $this->assertCount(1, $result->categories);
        $this->assertSame('feedback', $result->categories[0]->slug);
    }

    #[Test]
    public function it_detaches_category_from_widget(): void
    {
        $widget = Widget::factory()->create();
        $category = WidgetCategory::factory()->feedback()->create();
        $widget->categories()->attach($category->id);

        $result = $this->service->detach($widget->uuid, $category->uuid);

        $this->assertCount(0, $result->categories);
    }

    #[Test]
    public function it_syncs_categories_for_widget(): void
    {
        $widget = Widget::factory()->create();
        $feedback = WidgetCategory::factory()->feedback()->create();
        $analytics = WidgetCategory::factory()->analytics()->create();
        $widget->categories()->attach($feedback->id);

        $result = $this->service->sync(
            $widget->uuid,
            new SyncWidgetCategoriesDTO(category_uuids: [$analytics->uuid]),
        );

        $this->assertSame('analytics', $result->categories[0]->slug);
    }

    #[Test]
    public function it_throws_when_widget_category_is_not_found_by_uuid(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }

    #[Test]
    public function it_throws_when_widget_is_not_found_for_category_listing(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->listForWidget('00000000-0000-0000-0000-000000000000');
    }
}
