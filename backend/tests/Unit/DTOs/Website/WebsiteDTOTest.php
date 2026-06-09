<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Website;

use App\DTOs\Website\WebsiteDTO;
use App\Enums\WebsiteStatus;
use App\Http\Requests\ApiRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteDTOTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_website_model_to_dto_without_internal_ids(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $website = Website::factory()->create([
            'user_id' => $user->id,
            'name' => 'Acme Site',
            'url' => 'https://acme.example.com',
            'status' => WebsiteStatus::Active,
        ]);

        $dto = WebsiteDTO::fromModel($website);
        $array = $dto->toArray();

        $this->assertSame($website->uuid, $dto->uuid);
        $this->assertSame('Acme Site', $dto->name);
        $this->assertSame('https://acme.example.com', $dto->url);
        $this->assertSame(WebsiteStatus::Active, $dto->status);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('user_id', $array);
        $this->assertSame('active', $array['status']);
        $this->assertNotNull($array['created_at']);
    }

    #[Test]
    public function it_rejects_non_website_models(): void
    {
        $role = Role::factory()->customer()->create();

        $this->expectException(\InvalidArgumentException::class);

        WebsiteDTO::fromModel($role);
    }
}
