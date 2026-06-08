<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\PublicEntity;
use App\Repositories\Eloquent\EloquentRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('eloquent_repository_test_entities', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->string('name');
            $table->timestamps();
        });
    }

    #[Test]
    public function it_creates_and_finds_by_internal_id(): void
    {
        $repository = new EloquentRepositoryTestEntityRepository;

        $created = $repository->create(['name' => 'Acme']);

        $found = $repository->findById($created->id);

        $this->assertTrue($created->is($found));
        $this->assertSame('Acme', $found?->name);
    }

    #[Test]
    public function it_updates_a_model(): void
    {
        $repository = new EloquentRepositoryTestEntityRepository;
        $entity = $repository->create(['name' => 'Before']);

        $repository->update($entity, ['name' => 'After']);

        $this->assertSame('After', $entity->fresh()->name);
    }

    #[Test]
    public function it_deletes_a_model(): void
    {
        $repository = new EloquentRepositoryTestEntityRepository;
        $entity = $repository->create(['name' => 'Delete me']);

        $this->assertTrue($repository->delete($entity));
        $this->assertNull($repository->findById($entity->id));
    }

    #[Test]
    public function it_deletes_by_internal_id(): void
    {
        $repository = new EloquentRepositoryTestEntityRepository;
        $entity = $repository->create(['name' => 'Delete me']);

        $this->assertTrue($repository->deleteById($entity->id));
        $this->assertNull($repository->findById($entity->id));
    }

    #[Test]
    public function find_by_id_or_fail_throws_when_missing(): void
    {
        $repository = new EloquentRepositoryTestEntityRepository;

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $repository->findByIdOrFail(99999);
    }
}

/**
 * @internal
 */
final class EloquentRepositoryTestEntity extends PublicEntity
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $table = 'eloquent_repository_test_entities';

    protected $fillable = ['name'];
}

/**
 * @internal
 */
final class EloquentRepositoryTestEntityRepository extends EloquentRepository
{
    protected function model(): string
    {
        return EloquentRepositoryTestEntity::class;
    }
}
