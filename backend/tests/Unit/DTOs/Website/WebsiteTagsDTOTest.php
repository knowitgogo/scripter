<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Website;

use App\DTOs\Tag\TagDTO;
use App\DTOs\Website\WebsiteTagsDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteTagsDTOTest extends TestCase
{
    #[Test]
    public function it_exposes_website_uuid_and_tag_dtos(): void
    {
        $dto = WebsiteTagsDTO::forWebsite('550e8400-e29b-41d4-a716-446655440000', [
            new TagDTO(
                uuid: '660e8400-e29b-41d4-a716-446655440001',
                name: 'Marketing',
                slug: 'marketing',
            ),
        ]);

        $array = $dto->toArray();

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $dto->website_uuid);
        $this->assertCount(1, $dto->tags);
        $this->assertSame('marketing', $dto->tags[0]->slug);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $array['website_uuid']);
        $this->assertSame('marketing', $array['tags'][0]['slug']);
    }
}
