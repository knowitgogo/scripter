<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\DataTransferObject;
use App\Enums\RoleSlug;

/**
 * Command payload for assigning a role to a user.
 */
final class AssignRoleDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $userUuid,
        public readonly RoleSlug $roleSlug,
        public readonly ?string $actorUuid = null,
    ) {}
}
