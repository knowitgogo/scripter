<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\AuthTokenDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Enums\UserStatus;
use App\Events\Audit\GenericAuditEvent;
use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Audit\AuditDispatcher;

/**
 * Registers new customer accounts and issues an initial JWT.
 */
final class RegisterService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RoleRepositoryInterface $roles,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    public function register(RegisterDTO $registration): AuthTokenDTO
    {
        $role = $this->roles->findBySlugOrFail(RoleSlug::Customer);

        /** @var User $user */
        $user = $this->users->create([
            'role_id' => $role->id,
            'name' => $registration->name,
            'email' => $registration->email,
            'password' => $registration->password,
            'status' => UserStatus::Active,
        ]);

        $user->load('role');

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Created,
                subjectType: 'user',
                subjectUuid: $user->uuid,
                metadata: ['email' => $user->email],
            ),
        );

        $token = auth('api')->login($user);

        $this->users->update($user, ['last_login_at' => now()]);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Authenticated,
                subjectType: 'user',
                subjectUuid: $user->uuid,
                metadata: ['method' => 'jwt_register'],
            ),
        );

        return AuthTokenDTO::fromJwt($token, (int) config('jwt.ttl'));
    }
}
