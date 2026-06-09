<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use App\Enums\RoleSlug;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_seeds_default_roles(): void
    {
        (new RoleSeeder)->run();

        foreach (RoleSlug::seedOrder() as $slug) {
            $this->assertDatabaseHas('roles', [
                'slug' => $slug->value,
                'name' => $slug->label(),
            ]);
        }

        $this->assertSame(3, Role::query()->count());
    }

    #[Test]
    public function it_is_idempotent(): void
    {
        $seeder = new RoleSeeder;

        $seeder->run();
        $seeder->run();

        $this->assertSame(3, Role::query()->count());
    }
}
