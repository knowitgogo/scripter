<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\Widget;
use App\Models\WidgetTemplate;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Contracts\WidgetTemplateRepositoryInterface;
use App\Repositories\Eloquent\EloquentWidgetTemplateRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentWidgetTemplateRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentWidgetTemplateRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentWidgetTemplateRepository;
    }

    #[Test]
    public function it_implements_widget_template_and_uuid_repository_contracts(): void
    {
        $this->assertInstanceOf(WidgetTemplateRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function it_finds_widget_template_by_uuid_widget_and_slug(): void
    {
        $widget = Widget::factory()->create();
        $template = WidgetTemplate::factory()->for($widget)->embedded()->create();

        $this->assertTrue($template->is($this->repository->findByUuid($template->uuid)));
        $this->assertTrue($template->is($this->repository->findByWidgetAndSlug($widget->id, 'embedded')));
        $this->assertNull($this->repository->findByWidgetAndSlug($widget->id, 'missing'));
    }

    #[Test]
    public function it_lists_templates_for_widget_with_default_first(): void
    {
        $widget = Widget::factory()->create();
        WidgetTemplate::factory()->for($widget)->hosted()->create();
        WidgetTemplate::factory()->for($widget)->embedded()->defaultTemplate()->create();

        $templates = $this->repository->listForWidget($widget->id);

        $this->assertCount(2, $templates);
        $this->assertSame('embedded', $templates->first()->slug);
        $this->assertTrue($templates->first()->is_default);
    }

    #[Test]
    public function it_finds_default_template_for_widget(): void
    {
        $widget = Widget::factory()->create();
        WidgetTemplate::factory()->for($widget)->hosted()->create();
        $default = WidgetTemplate::factory()->for($widget)->embedded()->defaultTemplate()->create();

        $this->assertTrue($default->is($this->repository->findDefaultForWidget($widget->id)));
    }
}
