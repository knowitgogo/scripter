<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Canonical permission identifiers for authorization checks.
 */
enum Permission: string
{
    case WebsitesView = 'websites.view';
    case WebsitesManage = 'websites.manage';
    case TagsView = 'tags.view';
    case TagsManage = 'tags.manage';
    case WidgetsView = 'widgets.view';
    case WidgetsInstall = 'widgets.install';
    case AnalyticsView = 'analytics.view';
    case BillingView = 'billing.view';

    case AdminUsersView = 'admin.users.view';
    case AdminUsersManage = 'admin.users.manage';
    case AdminWebsitesManage = 'admin.websites.manage';
    case AdminWidgetsPublish = 'admin.widgets.publish';
    case AdminAnalyticsView = 'admin.analytics.view';
    case AdminBillingManage = 'admin.billing.manage';
    case AdminRolesAssign = 'admin.roles.assign';
    case AdminSystemManage = 'admin.system.manage';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
