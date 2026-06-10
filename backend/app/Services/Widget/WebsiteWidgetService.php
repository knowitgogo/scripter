<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\InstallWidgetDTO;
use App\DTOs\Widget\UpdateWebsiteWidgetDTO;
use App\DTOs\Widget\WebsiteWidgetDTO;
use App\Enums\AuditAction;
use App\Enums\WebsiteWidgetStatus;
use App\Enums\WidgetVersionStatus;
use App\Events\Audit\GenericAuditEvent;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteWidget;
use App\Models\WidgetVersion;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use App\Repositories\Contracts\WebsiteWidgetRepositoryInterface;
use App\Repositories\Contracts\WidgetVersionRepositoryInterface;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Manages widget installations on customer-owned websites.
 */
final class WebsiteWidgetService
{
    public function __construct(
        private readonly WebsiteRepositoryInterface $websites,
        private readonly WebsiteWidgetRepositoryInterface $websiteWidgets,
        private readonly WidgetVersionRepositoryInterface $widgetVersions,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    /**
     * @return list<WebsiteWidgetDTO>
     */
    public function listForUser(User $user): array
    {
        return $this->websiteWidgets->listForUser($user->id)
            ->map(fn (WebsiteWidget $websiteWidget): WebsiteWidgetDTO => WebsiteWidgetDTO::fromModel($websiteWidget))
            ->values()
            ->all();
    }

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
    public function getForUser(string $websiteWidgetUuid, User $user): WebsiteWidgetDTO
    {
        return WebsiteWidgetDTO::fromModel(
            $this->websiteWidgets->findByUuidForUserOrFail($websiteWidgetUuid, $user->id),
        );
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

    /**
     * @throws ModelNotFoundException
     * @throws DomainException
     */
    public function install(InstallWidgetDTO $payload, User $user): WebsiteWidgetDTO
    {
        $website = $this->resolveOwnedWebsite($payload->website_uuid, $user);

        /** @var WidgetVersion $widgetVersion */
        $widgetVersion = $this->widgetVersions->findByUuidOrFail($payload->widget_version_uuid);

        if ($widgetVersion->status !== WidgetVersionStatus::Published) {
            throw new DomainException('Only published widget versions can be installed.', 422);
        }

        if ($this->websiteWidgets->findByWebsiteAndWidgetVersion($website->id, $widgetVersion->id) !== null) {
            throw new DomainException('This widget version is already installed on the website.', 422);
        }

        /** @var WebsiteWidget $websiteWidget */
        $websiteWidget = $this->websiteWidgets->create([
            'website_id' => $website->id,
            'widget_version_id' => $widgetVersion->id,
            'status' => WebsiteWidgetStatus::Active,
            'configuration_json' => $payload->configuration,
        ]);

        $websiteWidget->load(['website', 'widgetVersion']);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Created,
                subjectType: 'website_widget',
                subjectUuid: $websiteWidget->uuid,
                actorUuid: $user->uuid,
                metadata: [
                    'website_uuid' => $website->uuid,
                    'widget_version_uuid' => $widgetVersion->uuid,
                ],
            ),
        );

        return WebsiteWidgetDTO::fromModel($websiteWidget);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function update(string $websiteWidgetUuid, UpdateWebsiteWidgetDTO $payload, User $user): WebsiteWidgetDTO
    {
        $websiteWidget = $this->websiteWidgets->findByUuidForUserOrFail($websiteWidgetUuid, $user->id);

        $attributes = $this->buildUpdateAttributes($payload);

        if ($attributes !== []) {
            $this->websiteWidgets->update($websiteWidget, $attributes);
            $websiteWidget->refresh();
            $websiteWidget->load(['website', 'widgetVersion']);

            $this->auditDispatcher->dispatch(
                GenericAuditEvent::record(
                    action: AuditAction::Updated,
                    subjectType: 'website_widget',
                    subjectUuid: $websiteWidget->uuid,
                    actorUuid: $user->uuid,
                    metadata: array_keys($attributes),
                ),
            );
        }

        return WebsiteWidgetDTO::fromModel($websiteWidget);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function uninstall(string $websiteWidgetUuid, User $user): void
    {
        $websiteWidget = $this->websiteWidgets->findByUuidForUserOrFail($websiteWidgetUuid, $user->id);
        $websiteWidget->load('website');

        $this->websiteWidgets->delete($websiteWidget);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Deleted,
                subjectType: 'website_widget',
                subjectUuid: $websiteWidgetUuid,
                actorUuid: $user->uuid,
                metadata: [
                    'website_uuid' => $websiteWidget->website->uuid,
                ],
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildUpdateAttributes(UpdateWebsiteWidgetDTO $payload): array
    {
        $attributes = [];

        if ($payload->status !== null) {
            $attributes['status'] = $payload->status;
        }

        if ($payload->configuration !== null) {
            $attributes['configuration_json'] = $payload->configuration;
        }

        return $attributes;
    }

    /**
     * @throws ModelNotFoundException
     */
    private function resolveOwnedWebsite(string $websiteUuid, User $user): Website
    {
        $website = $this->websites->findByUuidForUser($websiteUuid, $user->id);

        if ($website === null) {
            throw (new ModelNotFoundException)->setModel(Website::class);
        }

        return $website;
    }
}
