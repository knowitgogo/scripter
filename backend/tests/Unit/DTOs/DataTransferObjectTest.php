<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use App\DTOs\Concerns\MapsFromModel;
use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;
use App\Http\Requests\ApiRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DataTransferObjectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function to_array_returns_readonly_public_properties(): void
    {
        $dto = new class ('acme', 'active') extends DataTransferObject
        {
            public function __construct(
                public readonly string $name,
                public readonly string $status,
            ) {}
        };

        $this->assertSame([
            'name' => 'acme',
            'status' => 'active',
        ], $dto->toArray());
    }

    #[Test]
    public function json_serialize_matches_to_array(): void
    {
        $dto = new class ('v1') extends DataTransferObject
        {
            public function __construct(
                public readonly string $version,
            ) {}
        };

        $this->assertSame($dto->toArray(), $dto->jsonSerialize());
    }

    #[Test]
    public function to_array_transforms_nested_dtos(): void
    {
        $child = new class ('child-uuid') extends DataTransferObject
        {
            public function __construct(
                public readonly string $uuid,
            ) {}
        };

        $parent = new class ($child) extends DataTransferObject
        {
            public function __construct(
                public readonly DataTransferObject $item,
            ) {}
        };

        $this->assertSame([
            'item' => ['uuid' => 'child-uuid'],
        ], $parent->toArray());
    }

    #[Test]
    public function hidden_properties_are_excluded_from_to_array(): void
    {
        $dto = new class ('visible', 99) extends DataTransferObject
        {
            public function __construct(
                public readonly string $uuid,
                public readonly int $id,
            ) {}

            protected function hiddenProperties(): array
            {
                return ['id'];
            }
        };

        $this->assertSame(['uuid' => 'visible'], $dto->toArray());
    }

    #[Test]
    public function from_array_creates_dto_with_defaults(): void
    {
        $dto = ExampleDto::fromArray(['name' => 'Acme']);

        $this->assertSame('Acme', $dto->name);
        $this->assertSame('active', $dto->status);
    }

    #[Test]
    public function from_array_requires_mandatory_properties(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required DTO property [name]');

        ExampleDto::fromArray(['status' => 'active']);
    }

    #[Test]
    public function properties_are_readonly(): void
    {
        $reflection = new \ReflectionClass(ExampleDto::class);

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly(), $property->getName().' must be readonly');
        }
    }

    #[Test]
    public function maps_from_request_trait_uses_validated_input(): void
    {
        $request = new class extends ApiRequest
        {
            public function authorize(): bool
            {
                return true;
            }

            /**
             * @return array<string, mixed>
             */
            public function validationData(): array
            {
                return ['name' => 'From Request', 'status' => 'active'];
            }

            /**
             * @return array<string, mixed>
             */
            public function validated($key = null, $default = null): array
            {
                return $this->validationData();
            }
        };

        $dto = ExampleDto::fromRequest($request);

        $this->assertSame('From Request', $dto->name);
    }

    #[Test]
    public function maps_from_model_trait_excludes_internal_id(): void
    {
        $model = new class extends Model
        {
            protected $table = 'dto_test_models';

            /**
             * @var list<string>
             */
            protected $guarded = [];
        };

        $model->forceFill([
            'id' => 42,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'From Model',
        ]);

        $dto = PublicEntityDto::fromModel($model);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $dto->uuid);
        $this->assertSame('From Model', $dto->name);
        $this->assertArrayNotHasKey('id', $dto->toArray());
    }
}

/**
 * @internal
 */
final class ExampleDto extends DataTransferObject
{
    use MapsFromRequest;

    public function __construct(
        public readonly string $name,
        public readonly string $status = 'active',
    ) {}
}

/**
 * @internal
 */
final class PublicEntityDto extends DataTransferObject
{
    use MapsFromModel;

    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
    ) {}
}
