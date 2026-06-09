<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleSlug::seedOrder() as $slug) {
            Role::query()->firstOrCreate(
                ['slug' => $slug->value],
                ['name' => $slug->label()],
            );
        }
    }
}
