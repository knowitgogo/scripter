<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\ListWidgetCatalogQueryDTO;
use App\DTOs\Widget\RegisterWidgetDTO;
use App\DTOs\Widget\WidgetDTO;
use App\DTOs\Widget\WidgetVersionDTO;
use App\Enums\AuditAction;
use App\Enums\WidgetStatus;
use App\Events\Audit\GenericAuditEvent;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Models\Widget;
use App\Models\WidgetVersion;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use App\Repositories\Contracts\WidgetVersionRepositoryInterface;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Marketplace widget catalog operations for widgets and their releases.
 */
final class WidgetService
{
    public function __construct(
        private readonly WidgetRepositoryInterface $widgets,
        private readonly WidgetVersionRepositoryInterface $widgetVersions,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    /**
     * @return list<WidgetDTO>
     */
    public function listPublished(?ListWidgetCatalogQueryDTO $query = null): array
    {
        $query ??= new ListWidgetCatalogQueryDTO;

        return $this->widgets->listPublishedOrderedByName($query->normalizedSearch())
            ->map(fn (Widget $widget): WidgetDTO => WidgetDTO::fromModel($widget))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): WidgetDTO
    {
        /** @var Widget $widget */
        $widget = $this->widgets->findByUuidOrFail($uuid);

        return WidgetDTO::fromModel($widget);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getBySlug(string $slug): WidgetDTO
    {
        return WidgetDTO::fromModel($this->widgets->findBySlugOrFail($slug));
    }

    /**
     * @throws DomainException
     */
    public function register(RegisterWidgetDTO $payload, User $user): WidgetDTO
    {
        if ($this->widgets->findBySlug($payload->slug) !== null) {
            throw new DomainException('The slug has already been taken.', 422);
        }

        /** @var Widget $widget */
        $widget = $this->widgets->create([
            'name' => $payload->name,
            'slug' => $payload->slug,
            'description' => $payload->description,
            'status' => $payload->status ?? WidgetStatus::Draft,
        ]);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Created,
                subjectType: 'widget',
                subjectUuid: $widget->uuid,
                actorUuid: $user->uuid,
                metadata: ['slug' => $widget->slug],
            ),
        );

        return WidgetDTO::fromModel($widget);
    }

    /**
     * @throws ModelNotFoundException
     * @throws DomainException
     */
    public function activate(string $widgetUuid, User $user): WidgetDTO
    {
        /** @var Widget $widget */
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);

        if ($widget->status === WidgetStatus::Published) {
            throw new DomainException('The widget is already active.', 422);
        }

        if ($this->widgetVersions->findPublishedForWidget($widget->id) === null) {
            throw new DomainException('The widget cannot be activated without a published version.', 422);
        }

        $this->widgets->update($widget, ['status' => WidgetStatus::Published]);
        $widget->refresh();

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Published,
                subjectType: 'widget',
                subjectUuid: $widget->uuid,
                actorUuid: $user->uuid,
                metadata: ['slug' => $widget->slug],
            ),
        );

        return WidgetDTO::fromModel($widget);
    }

    /**
     * @throws ModelNotFoundException
     * @throws DomainException
     */
    public function deactivate(string $widgetUuid, User $user): WidgetDTO
    {
        /** @var Widget $widget */
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);

        if ($widget->status === WidgetStatus::Deprecated) {
            throw new DomainException('The widget is already deactivated.', 422);
        }

        if ($widget->status === WidgetStatus::Draft) {
            throw new DomainException('Draft widgets cannot be deactivated.', 422);
        }

        $this->widgets->update($widget, ['status' => WidgetStatus::Deprecated]);
        $widget->refresh();

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Deprecated,
                subjectType: 'widget',
                subjectUuid: $widget->uuid,
                actorUuid: $user->uuid,
                metadata: ['slug' => $widget->slug],
            ),
        );

        return WidgetDTO::fromModel($widget);
    }

    /**
     * @return list<WidgetVersionDTO>
     *
     * @throws ModelNotFoundException
     */
    public function listVersionsForWidget(string $widgetUuid): array
    {
        $widgetId = $this->widgets->findByUuidOrFail($widgetUuid)->id;

        return $this->widgetVersions->listForWidget($widgetId)
            ->map(fn (WidgetVersion $version): WidgetVersionDTO => WidgetVersionDTO::fromModel($version))
            ->values()
            ->all();
    }

    /**
     * @return list<WidgetVersionDTO>
     *
     * @throws ModelNotFoundException
     */
    public function listPublishedVersionsForWidget(string $widgetUuid): array
    {
        $widgetId = $this->widgets->findByUuidOrFail($widgetUuid)->id;

        return $this->widgetVersions->listPublishedForWidget($widgetId)
            ->map(fn (WidgetVersion $version): WidgetVersionDTO => WidgetVersionDTO::fromModel($version))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getVersionByUuid(string $uuid): WidgetVersionDTO
    {
        /** @var WidgetVersion $widgetVersion */
        $widgetVersion = $this->widgetVersions->findByUuidOrFail($uuid);
        $widgetVersion->load('widget');

        return WidgetVersionDTO::fromModel($widgetVersion);
    }
}
