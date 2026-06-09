<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\Permission;
use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\TestCase;

final class AuthorizationGateTest extends TestCase
{
    use InteractsWithAuthentication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function gate_allows_customer_website_permission(): void
    {
        $user = $this->createAuthUser();

        $this->assertTrue(Gate::forUser($user)->allows(Permission::WebsitesView->value));
        $this->assertFalse(Gate::forUser($user)->allows(Permission::AdminUsersView->value));
    }

    #[Test]
    public function gate_allows_admin_management_permissions(): void
    {
        $user = $this->createAuthUser([
            'role_id' => $this->adminRole()->id,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows(Permission::AdminUsersManage->value));
    }

    #[Test]
    public function gate_allows_super_admin_role_assignment_permission(): void
    {
        $role = Role::query()->where('slug', RoleSlug::SuperAdmin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue(Gate::forUser($user)->allows(Permission::AdminRolesAssign->value));
    }
}
