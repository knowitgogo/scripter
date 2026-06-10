<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WebsiteWidgetStatus;
use App\Models\Website;
use App\Models\WebsiteWidget;
use App\Models\WidgetVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebsiteWidget>
 */
class WebsiteWidgetFactory extends Factory
{
    protected $model = WebsiteWidget::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'widget_version_id' => WidgetVersion::factory()->published(),
            'status' => WebsiteWidgetStatus::Active,
            'configuration_json' => [
                'theme' => 'light',
                'position' => 'bottom-right',
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => WebsiteWidgetStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => WebsiteWidgetStatus::Inactive,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => WebsiteWidgetStatus::Suspended,
        ]);
    }

    /**
     * @param  array<string, mixed>  $configuration
     */
    public function configured(array $configuration): static
    {
        return $this->state(fn (): array => [
            'configuration_json' => $configuration,
        ]);
    }
}
