<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Persisted audit log record. Internal persistence model only.
 */
final class AuditLog extends PublicEntity
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'action',
        'subject_type',
        'subject_uuid',
        'actor_uuid',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
