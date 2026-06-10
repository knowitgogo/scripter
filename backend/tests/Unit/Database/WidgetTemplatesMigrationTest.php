<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetTemplatesMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widget_templates_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('widget_templates'));
        $this->assertTrue(Schema::hasColumns('widget_templates', [
            'id',
            'uuid',
            'widget_id',
            'name',
            'slug',
            'description',
            'content',
            'is_default',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function widget_templates_slug_is_unique_per_widget(): void
    {
        $indexes = Schema::getIndexes('widget_templates');
        $slugIndexes = array_filter(
            $indexes,
            fn (array $index): bool => $index['columns'] === ['widget_id', 'slug'] && $index['unique'],
        );

        $this->assertNotEmpty($slugIndexes);
    }
}
