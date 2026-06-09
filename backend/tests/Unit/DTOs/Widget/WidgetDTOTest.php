<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\WidgetDTO;
use App\Enums\WidgetStatus;
use App\Models\Role;
use App\Models\Widget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetDTOTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_widget_model_to_dto_without_internal_ids(): void
    {
        $widget = Widget::factory()->feedbackForm()->create();

        $dto = WidgetDTO::fromModel($widget);
        $array = $dto->toArray();

        $this->assertSame($widget->uuid, $dto->uuid);
        $this->assertSame('Feedback Form', $dto->name);
        $this->assertSame('feedback-form', $dto->slug);
        $this->assertSame(WidgetStatus::Published, $dto->status);
        $this->assertSame('Collect on-page feedback with customizable themes.', $dto->description);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertSame('published', $array['status']);
    }

    #[Test]
    public function it_rejects_non_widget_models(): void
    {
        $role = Role::factory()->customer()->create();

        $this->expectException(\InvalidArgumentException::class);

        WidgetDTO::fromModel($role);
    }
}
