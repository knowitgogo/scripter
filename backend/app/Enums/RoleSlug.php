<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Canonical role slugs used for authorization claims.
 */
enum RoleSlug: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Admin => 'Admin',
            self::SuperAdmin => 'Super Admin',
        };
    }

    /**
     * @return list<self>
     */
    public static function seedOrder(): array
    {
        return [
            self::Customer,
            self::Admin,
            self::SuperAdmin,
        ];
    }
}
