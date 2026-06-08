<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Concerns;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HidesInternalId;
use App\Models\PublicEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HasUuidTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('uuid_test_entities', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->string('name');
            $table->timestamps();
        });
    }

    #[Test]
    public function it_assigns_uuid_on_create(): void
    {
        $entity = UuidTestEntity::query()->create(['name' => 'Test']);

        $this->assertNotEmpty($entity->uuid);
        $this->assertTrue(\App\Support\UuidGenerator::isValid($entity->uuid));
    }

    #[Test]
    public function it_does_not_overwrite_existing_uuid_on_create(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $entity = UuidTestEntity::query()->create([
            'uuid' => $uuid,
            'name' => 'Test',
        ]);

        $this->assertSame($uuid, $entity->uuid);
    }

    #[Test]
    public function it_prevents_uuid_mutation_on_update(): void
    {
        $entity = UuidTestEntity::query()->create(['name' => 'Test']);
        $originalUuid = $entity->uuid;

        $entity->update(['uuid' => '550e8400-e29b-41d4-a716-446655440001']);

        $this->assertSame($originalUuid, $entity->fresh()->uuid);
    }

    #[Test]
    public function route_key_name_is_uuid(): void
    {
        $entity = new UuidTestEntity;

        $this->assertSame('uuid', $entity->getRouteKeyName());
    }

    #[Test]
    public function where_uuid_scope_finds_by_public_identifier(): void
    {
        $entity = UuidTestEntity::query()->create(['name' => 'Test']);

        $found = UuidTestEntity::query()->whereUuid($entity->uuid)->first();

        $this->assertTrue($entity->is($found));
    }
}

/**
 * @internal
 */
final class UuidTestEntity extends PublicEntity
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $table = 'uuid_test_entities';

    protected $fillable = ['uuid', 'name'];
}
