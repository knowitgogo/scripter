<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\AssignRoleDTO;
use App\DTOs\Auth\UserDTO;
use App\Enums\AuditAction;
use App\Events\Audit\GenericAuditEvent;
use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Audit\AuditDispatcher;
use App\Services\Infrastructure\CacheService;

/**
 * Assigns authorization roles to users and invalidates permission caches.
 */
final class RoleAssignmentService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RoleRepositoryInterface $roles,
        private readonly CacheService $cache,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    public function assign(AssignRoleDTO $command): UserDTO
    {
        /** @var User $user */
        $user = $this->users->findByUuidOrFail($command->userUuid);
        $user->loadMissing('role');

        $previousRoleSlug = $user->role?->slug;
        $role = $this->roles->findBySlugOrFail($command->roleSlug);

        if ($user->role_id === $role->id) {
            return UserDTO::fromModel($user);
        }

        $this->users->update($user, ['role_id' => $role->id]);
        $user->refresh()->load('role');

        $this->cache->forget('user_permissions', ['user_uuid' => $user->uuid]);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Updated,
                subjectType: 'user',
                subjectUuid: $user->uuid,
                actorUuid: $command->actorUuid,
                metadata: [
                    'previous_role' => $previousRoleSlug?->value,
                    'new_role' => $role->slug->value,
                ],
            ),
        );

        return UserDTO::fromModel($user);
    }
}
