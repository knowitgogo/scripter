<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\RoleSlug;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RoleSlugTest extends TestCase
{
    #[Test]
    public function it_exposes_canonical_slug_values(): void
    {
        $this->assertSame('customer', RoleSlug::Customer->value);
        $this->assertSame('admin', RoleSlug::Admin->value);
        $this->assertSame('super_admin', RoleSlug::SuperAdmin->value);
    }

    #[Test]
    public function it_provides_human_readable_labels(): void
    {
        $this->assertSame('Customer', RoleSlug::Customer->label());
        $this->assertSame('Admin', RoleSlug::Admin->label());
        $this->assertSame('Super Admin', RoleSlug::SuperAdmin->label());
    }

    #[Test]
    public function seed_order_includes_all_roles(): void
    {
        $this->assertSame(
            [RoleSlug::Customer, RoleSlug::Admin, RoleSlug::SuperAdmin],
            RoleSlug::seedOrder(),
        );
    }
}
