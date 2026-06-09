<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Eloquent;

use App\Models\Tag;
use App\Repositories\Contracts\EloquentRepositoryInterface;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Repositories\Contracts\UuidRepositoryInterface;
use App\Repositories\Eloquent\EloquentTagRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentTagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_tag_and_uuid_repository_contracts(): void
    {
        $repository = new EloquentTagRepository;

        $this->assertInstanceOf(TagRepositoryInterface::class, $repository);
        $this->assertInstanceOf(UuidRepositoryInterface::class, $repository);
        $this->assertInstanceOf(EloquentRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_finds_tag_by_uuid_and_slug(): void
    {
        $tag = Tag::factory()->marketing()->create();
        $repository = new EloquentTagRepository;

        $this->assertTrue($tag->is($repository->findByUuid($tag->uuid)));
        $this->assertTrue($tag->is($repository->findBySlug('marketing')));
        $this->assertNull($repository->findBySlug('missing'));
    }

    #[Test]
    public function it_lists_tags_ordered_by_name(): void
    {
        Tag::factory()->create(['name' => 'Zulu', 'slug' => 'zulu']);
        Tag::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);

        $repository = new EloquentTagRepository;
        $tags = $repository->listOrderedByName();

        $this->assertSame('Alpha', $tags->first()->name);
        $this->assertSame('Zulu', $tags->last()->name);
    }
}
