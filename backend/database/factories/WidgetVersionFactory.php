<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WidgetVersionStatus;
use App\Models\Widget;
use App\Models\WidgetVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WidgetVersion>
 */
class WidgetVersionFactory extends Factory
{
    protected $model = WidgetVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'widget_id' => Widget::factory(),
            'version' => fake()->numerify('#.#.#'),
            'status' => WidgetVersionStatus::Draft,
            'asset_manifest_url' => fake()->url().'/manifest.json',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => WidgetVersionStatus::Published,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => WidgetVersionStatus::Draft,
        ]);
    }

    public function deprecated(): static
    {
        return $this->state(fn (): array => [
            'status' => WidgetVersionStatus::Deprecated,
        ]);
    }

    public function release(string $version = '1.0.0'): static
    {
        return $this->state(fn (): array => [
            'version' => $version,
            'status' => WidgetVersionStatus::Published,
            'asset_manifest_url' => 'https://cdn.example.com/widgets/feedback-form/'.$version.'/manifest.json',
        ]);
    }
}
