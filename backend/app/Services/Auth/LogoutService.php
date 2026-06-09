<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\LogoutResultDTO;
use App\Enums\AuditAction;
use App\Events\Audit\GenericAuditEvent;
use App\Models\User;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Auth\AuthenticationException;

/**
 * Invalidates the current JWT and records a logout audit event.
 */
final class LogoutService
{
    public function __construct(
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    /**
     * @throws AuthenticationException
     */
    public function logout(): LogoutResultDTO
    {
        /** @var User|null $user */
        $user = auth('api')->user();

        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        auth('api')->logout();

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::LoggedOut,
                subjectType: 'user',
                subjectUuid: $user->uuid,
                metadata: ['method' => 'jwt'],
            ),
        );

        return LogoutResultDTO::success();
    }
}
