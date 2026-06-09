<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteTagsMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function website_tags_pivot_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('website_tags'));
        $this->assertTrue(Schema::hasColumns('website_tags', [
            'id',
            'website_id',
            'tag_id',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function website_tags_pivot_enforces_unique_website_and_tag_pair(): void
    {
        $indexes = Schema::getIndexes('website_tags');
        $pairIndexes = array_filter(
            $indexes,
            fn (array $index): bool => $index['unique']
                && in_array('website_id', $index['columns'], true)
                && in_array('tag_id', $index['columns'], true),
        );

        $this->assertNotEmpty($pairIndexes);
    }

    #[Test]
    public function website_tags_website_id_is_foreign_key_to_websites(): void
    {
        $foreignKeys = Schema::getForeignKeys('website_tags');
        $websiteForeignKeys = array_filter(
            $foreignKeys,
            fn (array $foreignKey): bool => in_array('website_id', $foreignKey['columns'], true)
                && $foreignKey['foreign_table'] === 'websites',
        );

        $this->assertNotEmpty($websiteForeignKeys);
    }

    #[Test]
    public function website_tags_tag_id_is_foreign_key_to_tags(): void
    {
        $foreignKeys = Schema::getForeignKeys('website_tags');
        $tagForeignKeys = array_filter(
            $foreignKeys,
            fn (array $foreignKey): bool => in_array('tag_id', $foreignKey['columns'], true)
                && $foreignKey['foreign_table'] === 'tags',
        );

        $this->assertNotEmpty($tagForeignKeys);
    }
}
