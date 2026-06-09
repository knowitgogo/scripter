<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Tag;

use App\Models\Tag;
use App\Repositories\Eloquent\EloquentTagRepository;
use App\Services\Tag\TagService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    private TagService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TagService(new EloquentTagRepository);
    }

    #[Test]
    public function it_lists_tags_as_dtos_ordered_by_name(): void
    {
        Tag::factory()->create(['name' => 'Zulu', 'slug' => 'zulu']);
        Tag::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);

        $tags = $this->service->list();

        $this->assertCount(2, $tags);
        $this->assertSame('Alpha', $tags[0]->name);
        $this->assertSame('Zulu', $tags[1]->name);
        $this->assertArrayNotHasKey('id', $tags[0]->toArray());
    }

    #[Test]
    public function it_returns_tag_dto_by_uuid(): void
    {
        $tag = Tag::factory()->marketing()->create();

        $dto = $this->service->getByUuid($tag->uuid);

        $this->assertSame('marketing', $dto->slug);
    }

    #[Test]
    public function it_returns_tag_dto_by_slug(): void
    {
        Tag::factory()->ecommerce()->create();

        $dto = $this->service->getBySlug('ecommerce');

        $this->assertSame('E-Commerce', $dto->name);
    }

    #[Test]
    public function it_throws_when_tag_is_not_found_by_uuid(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getByUuid('00000000-0000-0000-0000-000000000000');
    }
}
