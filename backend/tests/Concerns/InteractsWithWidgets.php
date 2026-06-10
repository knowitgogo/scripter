<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\User;
use App\Models\Website;
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function installWidgetPayload(Website $website, WidgetVersion $version, array $overrides = []): array
    {
        return array_merge([
            'website_uuid' => $website->uuid,
            'widget_version_uuid' => $version->uuid,
            'configuration' => [
                'theme' => 'dark',
                'position' => 'bottom-right',
            ],
        ], $overrides);
    }

    protected function createPublishedWidgetVersion(?Widget $widget = null): WidgetVersion
    {
        $widget ??= Widget::factory()->published()->create();

        return WidgetVersion::factory()->for($widget)->published()->create();
    }

    protected function createDraftWidgetVersion(
        Widget $widget,
        string $version = '1.0.0',
        ?string $assetManifestUrl = 'https://cdn.example.com/widgets/manifest.json',
    ): WidgetVersion {
        return WidgetVersion::factory()
            ->for($widget)
            ->draft()
            ->create([
                'version' => $version,
                'asset_manifest_url' => $assetManifestUrl,
            ]);
    }
}
