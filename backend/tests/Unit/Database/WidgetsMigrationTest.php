<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetsMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widgets_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('widgets'));
        $this->assertTrue(Schema::hasColumns('widgets', [
            'id',
            'uuid',
            'name',
            'slug',
            'description',
            'status',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function widgets_slug_column_is_unique(): void
    {
        $indexes = Schema::getIndexes('widgets');
        $slugIndexes = array_filter(
            $indexes,
            fn (array $index): bool => in_array('slug', $index['columns'], true) && $index['unique'],
        );

        $this->assertNotEmpty($slugIndexes);
    }

    #[Test]
    public function widgets_status_column_is_indexed(): void
    {
        $indexes = Schema::getIndexes('widgets');
        $statusIndexes = array_filter(
            $indexes,
            fn (array $index): bool => in_array('status', $index['columns'], true),
        );

        $this->assertNotEmpty($statusIndexes);
    }
}
