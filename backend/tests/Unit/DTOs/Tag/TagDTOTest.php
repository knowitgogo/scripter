<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Tag;

use App\DTOs\Tag\TagDTO;
use App\Models\Role;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TagDTOTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_tag_model_to_dto_without_internal_ids(): void
    {
        $tag = Tag::factory()->marketing()->create();

        $dto = TagDTO::fromModel($tag);
        $array = $dto->toArray();

        $this->assertSame($tag->uuid, $dto->uuid);
        $this->assertSame('Marketing', $dto->name);
        $this->assertSame('marketing', $dto->slug);
        $this->assertArrayNotHasKey('id', $array);
    }

    #[Test]
    public function it_rejects_non_tag_models(): void
    {
        $role = Role::factory()->customer()->create();

        $this->expectException(\InvalidArgumentException::class);

        TagDTO::fromModel($role);
    }
}
