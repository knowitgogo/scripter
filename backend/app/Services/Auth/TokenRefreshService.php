<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\AuthTokenDTO;
use App\Enums\AuditAction;
use App\Enums\UserStatus;
use App\Events\Audit\GenericAuditEvent;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Auth\AuthenticationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

/**
 * Issues a new JWT access token within the configured refresh window.
 */
final class TokenRefreshService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    /**
     * @throws AuthenticationException
     * @throws DomainException
     */
    public function refresh(): AuthTokenDTO
    {
        try {
            $newToken = auth('api')->parseToken()->refresh();
        } catch (JWTException $exception) {
            throw new AuthenticationException(
                $exception->getMessage() ?: 'Token could not be refreshed.',
            );
        }

        /** @var User|null $user */
        $user = auth('api')->setToken($newToken)->user();

        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $user = $this->users->findByUuidOrFail($user->uuid);

        if ($user->status !== UserStatus::Active) {
            throw new DomainException('Account is not active.', 403);
        }

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Authenticated,
                subjectType: 'user',
                subjectUuid: $user->uuid,
                metadata: ['method' => 'jwt_refresh'],
            ),
        );

        return AuthTokenDTO::fromJwt($newToken, (int) config('jwt.ttl'));
    }
}
