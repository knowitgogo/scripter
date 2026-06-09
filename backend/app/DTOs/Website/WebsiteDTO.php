<?php

declare(strict_types=1);

namespace App\DTOs\Website;

use App\DTOs\DataTransferObject;
use App\Enums\WebsiteStatus;
use App\Models\Website;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * Public website representation returned by Website domain services.
 */
final class WebsiteDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $url,
        public readonly WebsiteStatus $status,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof Website) {
            throw new \InvalidArgumentException('Expected instance of '.Website::class);
        }

        return new self(
            uuid: $model->uuid,
            name: $model->name,
            url: $model->url,
            status: $model->status,
            created_at: $model->created_at?->format(\DateTimeInterface::ATOM),
            updated_at: $model->updated_at?->format(\DateTimeInterface::ATOM),
        );
    }

    protected function transformValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return parent::transformValue($value);
    }
}
