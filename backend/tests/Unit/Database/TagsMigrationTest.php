<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TagsMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function tags_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('tags'));
        $this->assertTrue(Schema::hasColumns('tags', [
            'id',
            'uuid',
            'name',
            'slug',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function tags_slug_column_is_unique(): void
    {
        $indexes = Schema::getIndexes('tags');
        $slugIndexes = array_filter(
            $indexes,
            fn (array $index): bool => in_array('slug', $index['columns'], true) && $index['unique'],
        );

        $this->assertNotEmpty($slugIndexes);
    }

    #[Test]
    public function website_tag_pivot_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('website_tag'));
        $this->assertTrue(Schema::hasColumns('website_tag', [
            'id',
            'website_id',
            'tag_id',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function website_tag_pivot_enforces_unique_website_and_tag_pair(): void
    {
        $indexes = Schema::getIndexes('website_tag');
        $pairIndexes = array_filter(
            $indexes,
            fn (array $index): bool => $index['unique']
                && in_array('website_id', $index['columns'], true)
                && in_array('tag_id', $index['columns'], true),
        );

        $this->assertNotEmpty($pairIndexes);
    }
}
