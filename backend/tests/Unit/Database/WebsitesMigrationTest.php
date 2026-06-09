<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsitesMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function websites_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('websites'));
        $this->assertTrue(Schema::hasColumns('websites', [
            'id',
            'uuid',
            'user_id',
            'name',
            'url',
            'status',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function websites_url_column_is_unique(): void
    {
        $indexes = Schema::getIndexes('websites');
        $urlIndexes = array_filter(
            $indexes,
            fn (array $index): bool => in_array('url', $index['columns'], true) && $index['unique'],
        );

        $this->assertNotEmpty($urlIndexes);
    }

    #[Test]
    public function websites_user_id_is_foreign_key_to_users(): void
    {
        $foreignKeys = Schema::getForeignKeys('websites');
        $userForeignKeys = array_filter(
            $foreignKeys,
            fn (array $foreignKey): bool => in_array('user_id', $foreignKey['columns'], true)
                && $foreignKey['foreign_table'] === 'users',
        );

        $this->assertNotEmpty($userForeignKeys);
    }
}
