<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

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

        $this->assertDatabaseHas('roles', ['slug' => 'customer', 'name' => 'Customer']);
        $this->assertDatabaseHas('roles', ['slug' => 'admin', 'name' => 'Admin']);
        $this->assertDatabaseHas('roles', ['slug' => 'super_admin', 'name' => 'Super Admin']);
        $this->assertSame(3, Role::query()->count());
    }
}
