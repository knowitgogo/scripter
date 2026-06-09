<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Widget\ListWidgetCatalogQueryDTO;
use App\Enums\WidgetStatus;
use App\Models\Widget;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for {@see \App\Models\Widget} catalog aggregates.
 */
interface WidgetRepositoryInterface extends UuidRepositoryInterface
{
    public function findBySlug(string $slug): ?Widget;

    public function findBySlugOrFail(string $slug): Widget;

    /**
     * @return Collection<int, Widget>
     */
    public function listPublishedOrderedByName(?ListWidgetCatalogQueryDTO $query = null): Collection;

    /**
     * @return Collection<int, Widget>
     */
    public function listByStatus(WidgetStatus $status, ?ListWidgetCatalogQueryDTO $query = null): Collection;
}
