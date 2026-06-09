<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WidgetStatus;
use App\Models\Widget;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Widget>
 */
class WidgetFactory extends Factory
{
    protected $model = Widget::class;

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
            'status' => WidgetStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => WidgetStatus::Published,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => WidgetStatus::Draft,
        ]);
    }

    public function deprecated(): static
    {
        return $this->state(fn (): array => [
            'status' => WidgetStatus::Deprecated,
        ]);
    }

    public function feedbackForm(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Feedback Form',
            'slug' => 'feedback-form',
            'description' => 'Collect on-page feedback with customizable themes.',
            'status' => WidgetStatus::Published,
        ]);
    }
}
