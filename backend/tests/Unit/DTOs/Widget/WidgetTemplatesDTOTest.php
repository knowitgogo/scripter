<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Widget;

use App\DTOs\Widget\WidgetTemplateDTO;
use App\DTOs\Widget\WidgetTemplatesDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetTemplatesDTOTest extends TestCase
{
    #[Test]
    public function it_exposes_widget_uuid_and_templates_without_internal_ids(): void
    {
        $dto = WidgetTemplatesDTO::forWidget('550e8400-e29b-41d4-a716-446655440000', [
            new WidgetTemplateDTO(
                uuid: '660e8400-e29b-41d4-a716-446655440001',
                widget_uuid: '550e8400-e29b-41d4-a716-446655440000',
                name: 'Embedded Script',
                slug: 'embedded',
                content: '<script></script>',
                is_default: true,
            ),
        ]);

        $array = $dto->toArray();

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $dto->widget_uuid);
        $this->assertCount(1, $dto->templates);
        $this->assertSame('embedded', $array['templates'][0]['slug']);
    }
}
