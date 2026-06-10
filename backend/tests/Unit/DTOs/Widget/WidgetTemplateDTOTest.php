<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\WidgetTemplateDTO;
use App\Models\Role;
use App\Models\Widget;
use App\Models\WidgetTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetTemplateDTOTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_widget_template_model_to_dto_without_internal_ids(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();
        $template = WidgetTemplate::factory()->for($widget)->embedded()->defaultTemplate()->create();

        $dto = WidgetTemplateDTO::fromModel($template);
        $array = $dto->toArray();

        $this->assertSame($template->uuid, $dto->uuid);
        $this->assertSame($widget->uuid, $dto->widget_uuid);
        $this->assertSame('Embedded Script', $dto->name);
        $this->assertSame('embedded', $dto->slug);
        $this->assertTrue($dto->is_default);
        $this->assertStringContainsString('{{widget_key}}', $dto->content);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('widget_id', $array);
    }

    #[Test]
    public function it_rejects_non_widget_template_models(): void
    {
        $role = Role::factory()->customer()->create();

        $this->expectException(\InvalidArgumentException::class);

        WidgetTemplateDTO::fromModel($role);
    }
}
