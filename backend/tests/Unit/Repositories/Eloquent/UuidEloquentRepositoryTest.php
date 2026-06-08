<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\PublicEntity;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Eloquent\UuidEloquentRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UuidEloquentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('uuid_eloquent_repository_test_entities', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->string('name');
            $table->timestamps();
        });
    }

    #[Test]
    public function it_implements_uuid_and_eloquent_repository_contracts(): void
    {
        $repository = new UuidEloquentRepositoryTestEntityRepository;

        $this->assertInstanceOf(UuidRepositoryInterface::class, $repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_finds_entity_by_uuid(): void
    {
        $entity = UuidEloquentRepositoryTestEntity::query()->create(['name' => 'Test']);
        $repository = new UuidEloquentRepositoryTestEntityRepository;

        $found = $repository->findByUuid($entity->uuid);

        $this->assertTrue($entity->is($found));
    }

    #[Test]
    public function it_returns_null_for_invalid_uuid(): void
    {
        $repository = new UuidEloquentRepositoryTestEntityRepository;

        $this->assertNull($repository->findByUuid('invalid'));
    }

    #[Test]
    public function find_by_uuid_or_fail_throws_when_not_found(): void
    {
        $repository = new UuidEloquentRepositoryTestEntityRepository;

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $repository->findByUuidOrFail('550e8400-e29b-41d4-a716-446655440000');
    }
}

/**
 * @internal
 */
final class UuidEloquentRepositoryTestEntity extends PublicEntity
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $table = 'uuid_eloquent_repository_test_entities';

    protected $fillable = ['name'];
}

/**
 * @internal
 */
final class UuidEloquentRepositoryTestEntityRepository extends UuidEloquentRepository
{
    protected function model(): string
    {
        return UuidEloquentRepositoryTestEntity::class;
    }
}
