<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BlueprintMacrosTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function public_uuid_macro_creates_unique_uuid_column(): void
    {
        Schema::create('macro_test_entities', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->timestamps();
        });

        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        DB::table('macro_test_entities')->insert([
            'uuid' => $uuid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('macro_test_entities')->insert([
            'uuid' => $uuid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
