<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WidgetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WidgetCategory>
 */
class WidgetCategoryFactory extends Factory
{
    protected $model = WidgetCategory::class;

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
            'description' => fake()->sentence(),
        ];
    }

    public function feedback(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Feedback',
            'slug' => 'feedback',
            'description' => 'Widgets for collecting user feedback and surveys.',
        ]);
    }

    public function analytics(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Analytics',
            'slug' => 'analytics',
            'description' => 'Widgets for tracking engagement and metrics.',
        ]);
    }
}
