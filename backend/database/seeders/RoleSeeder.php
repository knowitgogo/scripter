<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, slug: string}>
     */
    private array $roles = [
        ['name' => 'Customer', 'slug' => 'customer'],
        ['name' => 'Admin', 'slug' => 'admin'],
        ['name' => 'Super Admin', 'slug' => 'super_admin'],
    ];

    public function run(): void
    {
        foreach ($this->roles as $role) {
            Role::query()->firstOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name']],
            );
        }
    }
}
