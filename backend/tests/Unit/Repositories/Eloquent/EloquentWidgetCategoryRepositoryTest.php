<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\WidgetCategory;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Contracts\WidgetCategoryRepositoryInterface;
use App\Repositories\Eloquent\EloquentWidgetCategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentWidgetCategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_widget_category_and_uuid_repository_contracts(): void
    {
        $repository = new EloquentWidgetCategoryRepository;

        $this->assertInstanceOf(WidgetCategoryRepositoryInterface::class, $repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_finds_widget_category_by_uuid_and_slug(): void
    {
        $category = WidgetCategory::factory()->feedback()->create();
        $repository = new EloquentWidgetCategoryRepository;

        $this->assertTrue($category->is($repository->findByUuid($category->uuid)));
        $this->assertTrue($category->is($repository->findBySlug('feedback')));
        $this->assertNull($repository->findBySlug('missing'));
    }

    #[Test]
    public function it_lists_widget_categories_ordered_by_name(): void
    {
        WidgetCategory::factory()->create(['name' => 'Zulu', 'slug' => 'zulu']);
        WidgetCategory::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);

        $repository = new EloquentWidgetCategoryRepository;
        $categories = $repository->listOrderedByName();

        $this->assertCount(2, $categories);
        $this->assertSame('Alpha', $categories->first()->name);
        $this->assertSame('Zulu', $categories->last()->name);
    }
}
