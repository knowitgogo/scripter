<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        $slug = Str::slug($name);

        return [
            'name' => Str::headline($name),
            'slug' => $slug,
        ];
    }

    public function marketing(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Marketing',
            'slug' => 'marketing',
        ]);
    }

    public function ecommerce(): static
    {
        return $this->state(fn (): array => [
            'name' => 'E-Commerce',
            'slug' => 'ecommerce',
        ]);
    }
}
