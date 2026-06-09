<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoleSlug;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = Str::slug(fake()->unique()->jobTitle());

        return [
            'name' => Str::headline($slug),
            'slug' => $slug,
        ];
    }

    public function customer(): static
    {
        return $this->state(fn (): array => [
            'name' => RoleSlug::Customer->label(),
            'slug' => RoleSlug::Customer,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (): array => [
            'name' => RoleSlug::Admin->label(),
            'slug' => RoleSlug::Admin,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (): array => [
            'name' => RoleSlug::SuperAdmin->label(),
            'slug' => RoleSlug::SuperAdmin,
        ]);
    }
}
