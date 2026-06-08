<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HidesInternalId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Eloquent model for domain entities with public UUID identifiers.
 *
 * Use this for new domain models (Website, Widget, Plan, etc.).
 * Authenticatable models (User) apply HasUuid and HidesInternalId directly.
 */
abstract class PublicEntity extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory, HasUuid, HidesInternalId;
}
