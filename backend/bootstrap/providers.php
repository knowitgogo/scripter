<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthorizationServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    AuthorizationServiceProvider::class,
    EventServiceProvider::class,
    RepositoryServiceProvider::class,
];
