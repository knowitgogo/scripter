<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetCategoryWidgetMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widget_category_widget_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('widget_category_widget'));
        $this->assertTrue(Schema::hasColumns('widget_category_widget', [
            'id',
            'widget_id',
            'widget_category_id',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function widget_category_widget_pair_is_unique(): void
    {
        $indexes = Schema::getIndexes('widget_category_widget');
        $pairIndexes = array_filter(
            $indexes,
            fn (array $index): bool => $index['unique']
                && in_array('widget_id', $index['columns'], true)
                && in_array('widget_category_id', $index['columns'], true),
        );

        $this->assertNotEmpty($pairIndexes);
    }

    #[Test]
    public function widget_category_widget_widget_category_id_is_indexed(): void
    {
        $indexes = Schema::getIndexes('widget_category_widget');
        $categoryIndexes = array_filter(
            $indexes,
            fn (array $index): bool => in_array('widget_category_id', $index['columns'], true),
        );

        $this->assertNotEmpty($categoryIndexes);
    }
}
