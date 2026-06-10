<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\WebsiteWidget;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for {@see \App\Models\WebsiteWidget} aggregates.
 */
interface WebsiteWidgetRepositoryInterface extends UuidRepositoryInterface
{
    public function findByUuidForWebsite(int $websiteId, string $uuid): ?WebsiteWidget;

    public function findByUuidForWebsiteOrFail(int $websiteId, string $uuid): WebsiteWidget;

    /**
     * @return Collection<int, WebsiteWidget>
     */
    public function listForWebsite(int $websiteId): Collection;
}
