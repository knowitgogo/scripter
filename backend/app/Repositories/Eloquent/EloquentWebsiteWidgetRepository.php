<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\WebsiteWidget;
use App\Repositories\Contracts\WebsiteWidgetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent persistence for website widget installation aggregates.
 */
final class EloquentWebsiteWidgetRepository extends UuidEloquentRepository implements WebsiteWidgetRepositoryInterface
{
    public function findByUuidForWebsite(int $websiteId, string $uuid): ?WebsiteWidget
    {
        /** @var WebsiteWidget|null $websiteWidget */
        $websiteWidget = $this->newModelQuery()
            ->with(['website', 'widgetVersion'])
            ->where('website_id', $websiteId)
            ->where('uuid', $uuid)
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

    protected function model(): string
    {
        return WebsiteWidget::class;
    }
}
