<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Widget;
use App\Models\WidgetTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WidgetTemplate>
 */
class WidgetTemplateFactory extends Factory
{
    protected $model = WidgetTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        $slug = Str::slug($name);

        return [
            'widget_id' => Widget::factory(),
            'name' => Str::headline($name),
            'slug' => $slug,
            'description' => fake()->sentence(),
            'content' => '<script src="{{cdn_url}}/widgets/{{widget_uuid}}.js" data-key="{{widget_key}}"></script>',
            'is_default' => false,
        ];
    }

    public function embedded(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Embedded Script',
            'slug' => 'embedded',
            'description' => 'Standard script-tag embed snippet.',
            'content' => '<script src="{{cdn_url}}/widgets/{{widget_uuid}}.js" data-key="{{widget_key}}" async></script>',
        ]);
    }

    public function hosted(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Hosted Iframe',
            'slug' => 'hosted',
            'description' => 'Iframe-based hosted widget snippet.',
            'content' => '<iframe src="{{hosted_url}}/widgets/{{widget_uuid}}?key={{widget_key}}" title="{{widget_name}}"></iframe>',
        ]);
    }

    public function defaultTemplate(): static
    {
        return $this->state(fn (): array => [
            'is_default' => true,
        ]);
    }
}
