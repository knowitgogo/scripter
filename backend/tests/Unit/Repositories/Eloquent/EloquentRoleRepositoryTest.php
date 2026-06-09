<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Eloquent\EloquentRoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentRoleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_role_and_uuid_repository_contracts(): void
    {
        $repository = new EloquentRoleRepository;

        $this->assertInstanceOf(RoleRepositoryInterface::class, $repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_finds_role_by_slug(): void
    {
        $role = Role::factory()->admin()->create();
        $repository = new EloquentRoleRepository;

        $found = $repository->findBySlug(RoleSlug::Admin);

        $this->assertTrue($role->is($found));
    }

    #[Test]
    public function find_by_slug_or_fail_throws_when_not_found(): void
    {
        $repository = new EloquentRoleRepository;

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $repository->findBySlugOrFail(RoleSlug::SuperAdmin);
    }
}
