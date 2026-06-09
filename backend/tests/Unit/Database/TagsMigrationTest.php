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
}
