<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\Models\Widget;
use App\Repositories\Eloquent\EloquentWidgetRepository;
use App\Services\Widget\WidgetCatalogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    private WidgetCatalogService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WidgetCatalogService(new EloquentWidgetRepository);
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
    public function it_returns_widget_dto_by_uuid(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();

        $dto = $this->service->getByUuid($widget->uuid);

        $this->assertSame('feedback-form', $dto->slug);
    }

    #[Test]
    public function it_returns_widget_dto_by_slug(): void
    {
        Widget::factory()->feedbackForm()->create();

        $dto = $this->service->getBySlug('feedback-form');

        $this->assertSame('Feedback Form', $dto->name);
    }

    #[Test]
    public function it_throws_when_widget_is_not_found_by_uuid(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }
}
