<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\User;
use App\Models\Widget;
use App\Models\WidgetVersion;

/**
 * Widget API helpers for feature and integration tests.
 */
trait InteractsWithWidgets
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, string>
     */
    protected function widgetPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Feedback Form',
            'slug' => 'feedback-form-'.fake()->unique()->numerify('####'),
            'description' => 'Collect on-page feedback with customizable themes.',
        ], $overrides);
    }

    protected function createWidgetFor(User $user, array $attributes = []): Widget
    {
        unset($user);

        return Widget::factory()->create($attributes);
    }

    protected function createDraftWidgetWithPublishedVersion(array $widgetAttributes = []): Widget
    {
        $widget = Widget::factory()->draft()->create($widgetAttributes);
        WidgetVersion::factory()->for($widget)->release('1.0.0')->create();

        return $widget;
    }
}
