<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\WebsiteWidgetDTO;
use App\Models\WebsiteWidget;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use App\Repositories\Contracts\WebsiteWidgetRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves widget installations on customer websites.
 */
final class WebsiteWidgetService
{
    public function __construct(
        private readonly WebsiteRepositoryInterface $websites,
        private readonly WebsiteWidgetRepositoryInterface $websiteWidgets,
    ) {}

    /**
     * @return list<WebsiteWidgetDTO>
     *
     * @throws ModelNotFoundException
     */
    public function listForWebsite(string $websiteUuid): array
    {
        $websiteId = $this->websites->findByUuidOrFail($websiteUuid)->id;

        return $this->websiteWidgets->listForWebsite($websiteId)
            ->map(fn (WebsiteWidget $websiteWidget): WebsiteWidgetDTO => WebsiteWidgetDTO::fromModel($websiteWidget))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): WebsiteWidgetDTO
    {
        /** @var WebsiteWidget $websiteWidget */
        $websiteWidget = $this->websiteWidgets->findByUuidOrFail($uuid);
        $websiteWidget->load(['website', 'widgetVersion']);

        return WebsiteWidgetDTO::fromModel($websiteWidget);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuidForWebsite(string $websiteUuid, string $uuid): WebsiteWidgetDTO
    {
        $website = $this->websites->findByUuidOrFail($websiteUuid);

        return WebsiteWidgetDTO::fromModel(
            $this->websiteWidgets->findByUuidForWebsiteOrFail($website->id, $uuid),
        );
    }
}
