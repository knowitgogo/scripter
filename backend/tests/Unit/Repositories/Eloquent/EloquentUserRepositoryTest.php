<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_user_and_uuid_repository_contracts(): void
    {
        $repository = new EloquentUserRepository;

        $this->assertInstanceOf(UserRepositoryInterface::class, $repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_finds_user_by_uuid(): void
    {
        $user = User::factory()->create();
        $repository = new EloquentUserRepository;

        $found = $repository->findByUuid($user->uuid);

        $this->assertTrue($user->is($found));
    }

    #[Test]
    public function it_finds_user_by_email(): void
    {
        $user = User::factory()->create(['email' => 'find-me@example.com']);
        $repository = new EloquentUserRepository;

        $found = $repository->findByEmail('find-me@example.com');

        $this->assertTrue($user->is($found));
        $this->assertNull($repository->findByEmail('missing@example.com'));
    }

    #[Test]
    public function it_updates_user_role_id(): void
    {
        $user = User::factory()->create();
        $repository = new EloquentUserRepository;

        $repository->update($user, ['name' => 'Updated Name']);

        $this->assertSame('Updated Name', $user->fresh()->name);
    }
}
