<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\DTOs\Website\CreateWebsiteDTO;
use App\DTOs\Website\UpdateWebsiteDTO;
use App\DTOs\Website\WebsiteDTO;
use App\Enums\AuditAction;
use App\Enums\WebsiteStatus;
use App\Events\Audit\GenericAuditEvent;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Models\Website;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Manages customer-owned websites.
 */
final class WebsiteService
{
    public function __construct(
        private readonly WebsiteRepositoryInterface $websites,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    /**
     * @return list<WebsiteDTO>
     */
    public function listForUser(User $user): array
    {
        return $this->websites->listForUser($user->id)
            ->map(fn (Website $website): WebsiteDTO => WebsiteDTO::fromModel($website))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getForUser(string $websiteUuid, User $user): WebsiteDTO
    {
        $website = $this->websites->findByUuidForUser($websiteUuid, $user->id);

        if ($website === null) {
            throw (new ModelNotFoundException)->setModel(Website::class);
        }

        return WebsiteDTO::fromModel($website);
    }

    /**
     * @throws DomainException
     */
    public function create(CreateWebsiteDTO $payload, User $user): WebsiteDTO
    {
        if ($this->websites->findByUrl($payload->url) !== null) {
            throw new DomainException('The url has already been taken.', 422);
        }

        /** @var Website $website */
        $website = $this->websites->create([
            'user_id' => $user->id,
            'name' => $payload->name,
            'url' => $payload->url,
            'status' => WebsiteStatus::Active,
        ]);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Created,
                subjectType: 'website',
                subjectUuid: $website->uuid,
                actorUuid: $user->uuid,
                metadata: ['url' => $website->url],
            ),
        );

        return WebsiteDTO::fromModel($website);
    }

    /**
     * @throws ModelNotFoundException
     * @throws DomainException
     */
    public function update(string $websiteUuid, UpdateWebsiteDTO $payload, User $user): WebsiteDTO
    {
        $website = $this->findOwnedWebsiteOrFail($websiteUuid, $user);

        if ($this->urlIsTakenByAnotherWebsite($payload->url, $website->uuid)) {
            throw new DomainException('The url has already been taken.', 422);
        }

        $this->websites->update($website, [
            'name' => $payload->name,
            'url' => $payload->url,
        ]);

        $website->refresh();

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Updated,
                subjectType: 'website',
                subjectUuid: $website->uuid,
                actorUuid: $user->uuid,
                metadata: ['url' => $website->url],
            ),
        );

        return WebsiteDTO::fromModel($website);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function delete(string $websiteUuid, User $user): void
    {
        $website = $this->findOwnedWebsiteOrFail($websiteUuid, $user);

        $this->websites->delete($website);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Deleted,
                subjectType: 'website',
                subjectUuid: $websiteUuid,
                actorUuid: $user->uuid,
            ),
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    private function findOwnedWebsiteOrFail(string $websiteUuid, User $user): Website
    {
        $website = $this->websites->findByUuidForUser($websiteUuid, $user->id);

        if ($website === null) {
            throw (new ModelNotFoundException)->setModel(Website::class);
        }

        return $website;
    }

    private function urlIsTakenByAnotherWebsite(string $url, string $websiteUuid): bool
    {
        $existing = $this->websites->findByUrl($url);

        return $existing !== null && $existing->uuid !== $websiteUuid;
    }
}
