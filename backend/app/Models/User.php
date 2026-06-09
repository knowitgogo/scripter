<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HidesInternalId;
use App\Support\Auth\JwtClaimBuilder;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['role_id', 'name', 'email', 'email_verified_at', 'password', 'status', 'last_login_at'])]
#[Hidden(['password', 'remember_token', 'id', 'role_id'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuid, HidesInternalId, Notifiable;

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->uuid;
    }

    /**
     * @return array<string, string>
     */
    public function getJWTCustomClaims(): array
    {
        return JwtClaimBuilder::forUser($this);
    }

    public function getAuthIdentifierName(): string
    {
        return 'uuid';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->uuid;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }
}
