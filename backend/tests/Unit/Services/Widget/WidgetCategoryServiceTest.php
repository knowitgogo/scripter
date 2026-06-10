<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\Models\WidgetCategory;
use App\Repositories\Eloquent\EloquentWidgetCategoryRepository;
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

        $this->service = new WidgetCategoryService(new EloquentWidgetCategoryRepository);
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
    public function it_throws_when_widget_category_is_not_found_by_uuid(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }
}
