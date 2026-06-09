<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Auth;

use App\DTOs\Auth\UserPermissionsDTO;
use App\Enums\Permission;
use App\Enums\RoleSlug;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserPermissionsDTOTest extends TestCase
{
    #[Test]
    public function it_checks_explicit_permissions(): void
    {
        $dto = new UserPermissionsDTO(
            user_uuid: '550e8400-e29b-41d4-a716-446655440000',
            role: RoleSlug::Customer,
            permissions: [Permission::WebsitesView->value],
        );

        $this->assertTrue($dto->allows(Permission::WebsitesView));
        $this->assertFalse($dto->allows(Permission::AdminUsersView));
    }

    #[Test]
    public function wildcard_grants_all_permissions(): void
    {
        $dto = new UserPermissionsDTO(
            user_uuid: '550e8400-e29b-41d4-a716-446655440000',
            role: RoleSlug::SuperAdmin,
            permissions: ['*'],
        );

        $this->assertTrue($dto->allows(Permission::AdminSystemManage));
    }

    #[Test]
    public function it_serializes_role_slug_as_string(): void
    {
        $dto = new UserPermissionsDTO(
            user_uuid: '550e8400-e29b-41d4-a716-446655440000',
            role: RoleSlug::Admin,
            permissions: [],
        );

        $this->assertSame('admin', $dto->toArray()['role']);
    }
}
