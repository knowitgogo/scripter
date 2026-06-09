<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetVersionsMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function widget_versions_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('widget_versions'));
        $this->assertTrue(Schema::hasColumns('widget_versions', [
            'id',
            'uuid',
            'widget_id',
            'version',
            'status',
            'asset_manifest_url',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function widget_versions_widget_id_and_version_pair_is_unique(): void
    {
        $indexes = Schema::getIndexes('widget_versions');
        $compositeIndexes = array_filter(
            $indexes,
            fn (array $index): bool => $index['unique']
                && in_array('widget_id', $index['columns'], true)
                && in_array('version', $index['columns'], true),
        );

        $this->assertNotEmpty($compositeIndexes);
    }

    #[Test]
    public function widget_versions_status_column_is_indexed(): void
    {
        $indexes = Schema::getIndexes('widget_versions');
        $statusIndexes = array_filter(
            $indexes,
            fn (array $index): bool => in_array('status', $index['columns'], true),
        );

        $this->assertNotEmpty($statusIndexes);
    }
}
