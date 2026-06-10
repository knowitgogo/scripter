<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\WebsiteWidget;
use App\Repositories\Contracts\WebsiteWidgetRepositoryInterface;
use App\Support\UuidGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent persistence for website widget installation aggregates.
 */
final class EloquentWebsiteWidgetRepository extends UuidEloquentRepository implements WebsiteWidgetRepositoryInterface
{
    public function findByWebsiteAndWidgetVersion(int $websiteId, int $widgetVersionId): ?WebsiteWidget
    {
        /** @var WebsiteWidget|null $websiteWidget */
        $websiteWidget = $this->newModelQuery()
            ->with(['website', 'widgetVersion'])
            ->where('website_id', $websiteId)
            ->where('widget_version_id', $widgetVersionId)
            ->first();

        return $websiteWidget;
    }

    public function findByUuidForWebsite(int $websiteId, string $uuid): ?WebsiteWidget
    {
        if (! UuidGenerator::isValid($uuid)) {
            return null;
        }

        /** @var WebsiteWidget|null $websiteWidget */
        $websiteWidget = $this->newModelQuery()
            ->with(['website', 'widgetVersion'])
            ->where('website_id', $websiteId)
            ->whereUuid($uuid)
            ->first();

        return $websiteWidget;
    }

    public function findByUuidForWebsiteOrFail(int $websiteId, string $uuid): WebsiteWidget
    {
        $websiteWidget = $this->findByUuidForWebsite($websiteId, $uuid);

        if ($websiteWidget === null) {
            throw (new ModelNotFoundException)->setModel($this->model());
        }

        return $websiteWidget;
    }

    public function findByUuidForUser(string $uuid, int $userId): ?WebsiteWidget
    {
        if (! UuidGenerator::isValid($uuid)) {
            return null;
        }

        /** @var WebsiteWidget|null $websiteWidget */
        $websiteWidget = $this->newModelQuery()
            ->with(['website', 'widgetVersion'])
            ->whereUuid($uuid)
            ->whereHas('website', fn ($query) => $query->where('user_id', $userId))
            ->first();

        return $websiteWidget;
    }

    public function findByUuidForUserOrFail(string $uuid, int $userId): WebsiteWidget
    {
        $websiteWidget = $this->findByUuidForUser($uuid, $userId);

        if ($websiteWidget === null) {
            throw (new ModelNotFoundException)->setModel($this->model());
        }

        return $websiteWidget;
    }

    /**
     * @return Collection<int, WebsiteWidget>
     */
    public function listForWebsite(int $websiteId): Collection
    {
        return $this->newModelQuery()
            ->with(['website', 'widgetVersion'])
            ->where('website_id', $websiteId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, WebsiteWidget>
     */
    public function listForUser(int $userId): Collection
    {
        return $this->newModelQuery()
            ->with(['website', 'widgetVersion'])
            ->whereHas('website', fn ($query) => $query->where('user_id', $userId))
            ->orderByDesc('created_at')
            ->get();
    }

    protected function model(): string
    {
        return WebsiteWidget::class;
    }
}
