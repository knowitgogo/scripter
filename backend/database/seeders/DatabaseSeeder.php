<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        User::factory()->create([
            'role_id' => Role::query()->where('slug', 'customer')->value('id'),
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
