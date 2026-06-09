<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Enums\WidgetStatus;
use App\Models\Widget;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use App\Repositories\Eloquent\EloquentWidgetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentWidgetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_widget_and_uuid_repository_contracts(): void
    {
        $repository = new EloquentWidgetRepository;

        $this->assertInstanceOf(WidgetRepositoryInterface::class, $repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_finds_widget_by_uuid_and_slug(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();
        $repository = new EloquentWidgetRepository;

        $this->assertTrue($widget->is($repository->findByUuid($widget->uuid)));
        $this->assertTrue($widget->is($repository->findBySlug('feedback-form')));
        $this->assertNull($repository->findBySlug('missing'));
    }

    #[Test]
    public function it_lists_published_widgets_ordered_by_name(): void
    {
        Widget::factory()->published()->create(['name' => 'Zulu Widget', 'slug' => 'zulu-widget']);
        Widget::factory()->published()->create(['name' => 'Alpha Widget', 'slug' => 'alpha-widget']);
        Widget::factory()->draft()->create(['name' => 'Draft Widget', 'slug' => 'draft-widget']);

        $repository = new EloquentWidgetRepository;
        $widgets = $repository->listPublishedOrderedByName();

        $this->assertCount(2, $widgets);
        $this->assertSame('Alpha Widget', $widgets->first()->name);
        $this->assertSame('Zulu Widget', $widgets->last()->name);
    }

    #[Test]
    public function it_lists_widgets_by_status(): void
    {
        Widget::factory()->deprecated()->create(['slug' => 'deprecated-widget']);
        Widget::factory()->draft()->create(['slug' => 'draft-only']);

        $repository = new EloquentWidgetRepository;

        $this->assertCount(1, $repository->listByStatus(WidgetStatus::Deprecated));
        $this->assertCount(1, $repository->listByStatus(WidgetStatus::Draft));
    }
}
