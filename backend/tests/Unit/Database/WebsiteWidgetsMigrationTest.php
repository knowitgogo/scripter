<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteWidgetsMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function website_widgets_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('website_widgets'));
        $this->assertTrue(Schema::hasColumns('website_widgets', [
            'id',
            'uuid',
            'website_id',
            'widget_version_id',
            'status',
            'configuration_json',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function website_widgets_enforces_unique_install_per_website_and_version(): void
    {
        $indexes = Schema::getIndexes('website_widgets');
        $uniqueIndexes = array_filter(
            $indexes,
            fn (array $index): bool => $index['columns'] === ['website_id', 'widget_version_id'] && $index['unique'],
        );

        $this->assertNotEmpty($uniqueIndexes);
    }
}
