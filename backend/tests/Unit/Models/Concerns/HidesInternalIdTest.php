<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Concerns;

use App\Models\Concerns\HidesInternalId;
use App\Models\PublicEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HidesInternalIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('hidden_id_test_entities', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->string('name');
            $table->timestamps();
        });
    }

    #[Test]
    public function internal_id_is_hidden_from_array_serialization(): void
    {
        $entity = HiddenIdTestEntity::query()->create(['name' => 'Test']);

        $array = $entity->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function internal_id_is_hidden_from_json_serialization(): void
    {
        $entity = HiddenIdTestEntity::query()->create(['name' => 'Test']);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($entity->toJson(), true);

        $this->assertArrayNotHasKey('id', $decoded);
        $this->assertArrayHasKey('uuid', $decoded);
    }
}

/**
 * @internal
 */
final class HiddenIdTestEntity extends PublicEntity
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $table = 'hidden_id_test_entities';

    protected $fillable = ['name'];
}
