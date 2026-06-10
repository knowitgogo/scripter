<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetCategoriesMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widget_categories_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('widget_categories'));
        $this->assertTrue(Schema::hasColumns('widget_categories', [
            'id',
            'uuid',
            'name',
            'slug',
            'description',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function widget_categories_slug_column_is_unique(): void
    {
        $indexes = Schema::getIndexes('widget_categories');
        $slugIndexes = array_filter(
            $indexes,
            fn (array $index): bool => in_array('slug', $index['columns'], true) && $index['unique'],
        );

        $this->assertNotEmpty($slugIndexes);
    }
}
