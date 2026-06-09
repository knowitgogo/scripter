<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\AuthTokenDTO;
use App\DTOs\Auth\LoginDTO;
use App\Enums\AuditAction;
use App\Enums\UserStatus;
use App\Events\Audit\GenericAuditEvent;
use App\Exceptions\DomainException;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

/**
 * Authenticates users and issues JWT access tokens.
 */
final class LoginService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    /**
     * @throws AuthenticationException
     * @throws DomainException
     */
    public function login(LoginDTO $credentials): AuthTokenDTO
    {
        $user = $this->users->findByEmail($credentials->email);

        if ($user === null || ! Hash::check($credentials->password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if ($user->status !== UserStatus::Active) {
            throw new DomainException('Account is not active.', 403);
        }

        $token = auth('api')->login($user);

        $this->users->update($user, ['last_login_at' => now()]);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Authenticated,
                subjectType: 'user',
                subjectUuid: $user->uuid,
                metadata: ['method' => 'jwt'],
            ),
        );

        return AuthTokenDTO::fromJwt($token, (int) config('jwt.ttl'));
    }
}
