<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksPermissions;
use App\Services\Auth\PermissionService;

/**
 * Base policy with injected permission resolution.
 */
abstract class BasePolicy
{
    use ChecksPermissions;

    public function __construct(
        protected readonly PermissionService $permissionService,
    ) {}

    protected function permissionService(): PermissionService
    {
        return $this->permissionService;
    }
}
