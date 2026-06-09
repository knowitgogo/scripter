<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Permissions;

use App\Enums\Permission;
use App\Enums\RoleSlug;
use App\Repositories\Permissions\ConfigPermissionsRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ConfigPermissionsRepositoryTest extends TestCase
{
    private ConfigPermissionsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ConfigPermissionsRepository;
    }

    #[Test]
    public function it_returns_customer_permissions(): void
    {
        $permissions = $this->repository->forRole(RoleSlug::Customer);

        $this->assertContains(Permission::WebsitesView->value, $permissions);
        $this->assertNotContains(Permission::AdminUsersView->value, $permissions);
    }

    #[Test]
    public function it_returns_admin_permissions_including_admin_scopes(): void
    {
        $permissions = $this->repository->forRole(RoleSlug::Admin);

        $this->assertContains(Permission::WebsitesView->value, $permissions);
        $this->assertContains(Permission::AdminUsersManage->value, $permissions);
        $this->assertNotContains(Permission::AdminRolesAssign->value, $permissions);
    }

    #[Test]
    public function it_expands_super_admin_wildcard_to_all_permissions(): void
    {
        $permissions = $this->repository->forRole(RoleSlug::SuperAdmin);

        $this->assertSame(Permission::values(), $permissions);
    }
}
