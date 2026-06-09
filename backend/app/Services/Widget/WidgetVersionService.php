<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\WidgetVersionDTO;
use App\Enums\AuditAction;
use App\Enums\WidgetVersionStatus;
use App\Events\Audit\GenericAuditEvent;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Models\WidgetVersion;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use App\Repositories\Contracts\WidgetVersionRepositoryInterface;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves widget releases from the marketplace catalog.
 */
final class WidgetVersionService
{
    public function __construct(
        private readonly WidgetRepositoryInterface $widgets,
        private readonly WidgetVersionRepositoryInterface $widgetVersions,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    /**
     * @return list<WidgetVersionDTO>
     */
    public function listForWidget(string $widgetUuid): array
    {
        $widgetId = $this->widgets->findByUuidOrFail($widgetUuid)->id;

        return $this->widgetVersions->listForWidget($widgetId)
            ->map(fn (WidgetVersion $version): WidgetVersionDTO => WidgetVersionDTO::fromModel($version))
            ->values()
            ->all();
    }

    /**
     * @return list<WidgetVersionDTO>
     */
    public function listPublishedForWidget(string $widgetUuid): array
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
    public function getByUuid(string $uuid): WidgetVersionDTO
    {
        /** @var WidgetVersion $widgetVersion */
        $widgetVersion = $this->widgetVersions->findByUuidOrFail($uuid);
        $widgetVersion->load('widget');

        return WidgetVersionDTO::fromModel($widgetVersion);
    }

    /**
     * @throws ModelNotFoundException
     * @throws DomainException
     */
    public function publish(string $versionUuid, User $user): WidgetVersionDTO
    {
        /** @var WidgetVersion $widgetVersion */
        $widgetVersion = $this->widgetVersions->findByUuidOrFail($versionUuid);
        $widgetVersion->load('widget');

        if ($widgetVersion->status === WidgetVersionStatus::Published) {
            throw new DomainException('The widget version is already published.', 422);
        }

        if ($widgetVersion->status === WidgetVersionStatus::Deprecated) {
            throw new DomainException('Deprecated widget versions cannot be published.', 422);
        }

        if ($widgetVersion->asset_manifest_url === null || trim($widgetVersion->asset_manifest_url) === '') {
            throw new DomainException('The widget version cannot be published without an asset manifest URL.', 422);
        }

        $this->replacePublishedVersion($widgetVersion);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Published,
                subjectType: 'widget_version',
                subjectUuid: $widgetVersion->uuid,
                actorUuid: $user->uuid,
                metadata: [
                    'widget_uuid' => $widgetVersion->widget->uuid,
                    'version' => $widgetVersion->version,
                ],
            ),
        );

        return WidgetVersionDTO::fromModel($widgetVersion);
    }

    /**
     * @throws ModelNotFoundException
     * @throws DomainException
     */
    public function rollback(string $versionUuid, User $user): WidgetVersionDTO
    {
        /** @var WidgetVersion $widgetVersion */
        $widgetVersion = $this->widgetVersions->findByUuidOrFail($versionUuid);
        $widgetVersion->load('widget');

        if ($widgetVersion->status !== WidgetVersionStatus::Deprecated) {
            throw new DomainException('Only deprecated widget versions can be rolled back.', 422);
        }

        if ($widgetVersion->asset_manifest_url === null || trim($widgetVersion->asset_manifest_url) === '') {
            throw new DomainException('The widget version cannot be rolled back without an asset manifest URL.', 422);
        }

        $currentPublished = $this->widgetVersions->findPublishedForWidget($widgetVersion->widget_id);

        $metadata = [
            'widget_uuid' => $widgetVersion->widget->uuid,
            'version' => $widgetVersion->version,
        ];

        if ($currentPublished !== null) {
            $metadata['replaced_version_uuid'] = $currentPublished->uuid;
            $metadata['replaced_version'] = $currentPublished->version;
        }

        $this->replacePublishedVersion($widgetVersion);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Restored,
                subjectType: 'widget_version',
                subjectUuid: $widgetVersion->uuid,
                actorUuid: $user->uuid,
                metadata: $metadata,
            ),
        );

        return WidgetVersionDTO::fromModel($widgetVersion);
    }

    /**
     * @throws ModelNotFoundException
     * @throws DomainException
     */
    public function deprecate(string $versionUuid, User $user): WidgetVersionDTO
    {
        /** @var WidgetVersion $widgetVersion */
        $widgetVersion = $this->widgetVersions->findByUuidOrFail($versionUuid);
        $widgetVersion->load('widget');

        if ($widgetVersion->status === WidgetVersionStatus::Deprecated) {
            throw new DomainException('The widget version is already deprecated.', 422);
        }

        if ($widgetVersion->status === WidgetVersionStatus::Draft) {
            throw new DomainException('Draft widget versions cannot be deprecated.', 422);
        }

        $this->widgetVersions->update($widgetVersion, ['status' => WidgetVersionStatus::Deprecated]);
        $widgetVersion->refresh();

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Deprecated,
                subjectType: 'widget_version',
                subjectUuid: $widgetVersion->uuid,
                actorUuid: $user->uuid,
                metadata: [
                    'widget_uuid' => $widgetVersion->widget->uuid,
                    'version' => $widgetVersion->version,
                ],
            ),
        );

        return WidgetVersionDTO::fromModel($widgetVersion);
    }

    private function replacePublishedVersion(WidgetVersion $widgetVersion): void
    {
        foreach ($this->widgetVersions->listPublishedForWidget($widgetVersion->widget_id) as $published) {
            if ($published->id !== $widgetVersion->id) {
                $this->widgetVersions->update($published, ['status' => WidgetVersionStatus::Deprecated]);
            }
        }

        $this->widgetVersions->update($widgetVersion, ['status' => WidgetVersionStatus::Published]);
        $widgetVersion->refresh();
    }
}
