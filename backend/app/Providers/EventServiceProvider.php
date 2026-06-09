<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Audit\Contracts\AuditEventInterface;
use App\Listeners\RecordAuditLog;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Registers domain and audit event listeners.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, list<class-string>>
     */
    protected $listen = [
        AuditEventInterface::class => [
            RecordAuditLog::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
