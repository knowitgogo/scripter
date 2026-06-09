<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Events\Audit\Contracts\AuditEventInterface;
use Illuminate\Support\Facades\Event;

/**
 * Dispatches audit events into the application event bus.
 */
final class AuditDispatcher
{
    public function dispatch(AuditEventInterface $event): void
    {
        if (! config('audit.enabled')) {
            return;
        }

        Event::dispatch($event);
    }
}
