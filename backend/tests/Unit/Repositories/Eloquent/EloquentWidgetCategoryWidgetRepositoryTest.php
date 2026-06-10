<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\Widget;
use App\Models\WidgetCategory;
use App\Repositories\Eloquent\EloquentWidgetCategoryWidgetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentWidgetCategoryWidgetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentWidgetCategoryWidgetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentWidgetCategoryWidgetRepository;
    }

    #[Test]
    public function it_attaches_and_lists_categories_for_widget(): void
    {
        $widget = Widget::factory()->create();
        $category = WidgetCategory::factory()->feedback()->create();

        $this->repository->attach($widget->id, $category->id);

        $categories = $this->repository->listCategoriesForWidget($widget->id);

        $this->assertCount(1, $categories);
        $this->assertSame('feedback', $categories->first()->slug);
        $this->assertTrue($this->repository->isAttached($widget->id, $category->id));
    }

    #[Test]
    public function it_detaches_category_from_widget(): void
    {
        $widget = Widget::factory()->create();
        $category = WidgetCategory::factory()->feedback()->create();
        $widget->categories()->attach($category->id);

        $this->repository->detach($widget->id, $category->id);

        $this->assertCount(0, $this->repository->listCategoriesForWidget($widget->id));
        $this->assertFalse($this->repository->isAttached($widget->id, $category->id));
    }

    #[Test]
    public function it_syncs_widget_categories(): void
    {
        $widget = Widget::factory()->create();
        $feedback = WidgetCategory::factory()->feedback()->create();
        $analytics = WidgetCategory::factory()->analytics()->create();
        $widget->categories()->attach($feedback->id);

        $this->repository->sync($widget->id, [$analytics->id]);

        $categories = $this->repository->listCategoriesForWidget($widget->id);

        $this->assertCount(1, $categories);
        $this->assertSame('analytics', $categories->first()->slug);
    }
}
