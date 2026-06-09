<?php

declare(strict_types=1);

use App\Enums\Permission;

return [

    /*
    |--------------------------------------------------------------------------
    | Role Permissions
    |--------------------------------------------------------------------------
    |
    | Maps role slugs to granted permission identifiers. Super admins use the
    | wildcard "*" which expands to all Permission enum values at runtime.
    |
    */

    'roles' => [
        'customer' => [
            Permission::WebsitesView->value,
            Permission::WebsitesManage->value,
            Permission::TagsView->value,
            Permission::TagsManage->value,
            Permission::WidgetsView->value,
            Permission::WidgetsInstall->value,
            Permission::AnalyticsView->value,
            Permission::BillingView->value,
        ],
        'admin' => [
            Permission::WebsitesView->value,
            Permission::WebsitesManage->value,
            Permission::TagsView->value,
            Permission::TagsManage->value,
            Permission::WidgetsView->value,
            Permission::WidgetsInstall->value,
            Permission::AnalyticsView->value,
            Permission::BillingView->value,
            Permission::AdminUsersView->value,
            Permission::AdminUsersManage->value,
            Permission::AdminWebsitesManage->value,
            Permission::AdminWidgetsPublish->value,
            Permission::AdminAnalyticsView->value,
            Permission::AdminBillingManage->value,
        ],
        'super_admin' => [
            '*',
        ],
    ],

];
