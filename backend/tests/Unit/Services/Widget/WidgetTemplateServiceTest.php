<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\Models\Widget;
use App\Models\WidgetTemplate;
use App\Repositories\Eloquent\EloquentWidgetRepository;
use App\Repositories\Eloquent\EloquentWidgetTemplateRepository;
use App\Services\Widget\WidgetTemplateService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    private WidgetTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WidgetTemplateService(
            new EloquentWidgetRepository,
            new EloquentWidgetTemplateRepository,
        );
    }

    #[Test]
    public function it_lists_templates_for_widget_as_dtos(): void
    {
        $widget = Widget::factory()->create();
        WidgetTemplate::factory()->for($widget)->embedded()->defaultTemplate()->create();
        WidgetTemplate::factory()->for($widget)->hosted()->create();

        $templates = $this->service->listForWidget($widget->uuid);

        $this->assertCount(2, $templates);
        $this->assertSame('embedded', $templates[0]->slug);
        $this->assertTrue($templates[0]->is_default);
        $this->assertArrayNotHasKey('id', $templates[0]->toArray());
    }

    #[Test]
    public function it_returns_widget_template_dto_by_uuid_and_slug(): void
    {
        $widget = Widget::factory()->create();
        $template = WidgetTemplate::factory()->for($widget)->embedded()->create();

        $byUuid = $this->service->getByUuid($template->uuid);
        $bySlug = $this->service->getByWidgetAndSlug($widget->uuid, 'embedded');

        $this->assertSame('embedded', $byUuid->slug);
        $this->assertSame($widget->uuid, $bySlug->widget_uuid);
    }

    #[Test]
    public function it_returns_default_template_for_widget(): void
    {
        $widget = Widget::factory()->create();
        WidgetTemplate::factory()->for($widget)->hosted()->create();
        WidgetTemplate::factory()->for($widget)->embedded()->defaultTemplate()->create();

        $template = $this->service->getDefaultForWidget($widget->uuid);

        $this->assertSame('embedded', $template->slug);
        $this->assertTrue($template->is_default);
    }

    #[Test]
    public function it_throws_when_widget_template_is_not_found_by_uuid(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }

    #[Test]
    public function it_throws_when_widget_is_not_found_for_template_listing(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->listForWidget('00000000-0000-0000-0000-000000000000');
    }

    #[Test]
    public function it_throws_when_default_template_is_missing(): void
    {
        $widget = Widget::factory()->create();
        WidgetTemplate::factory()->for($widget)->hosted()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->getDefaultForWidget($widget->uuid);
    }
}
