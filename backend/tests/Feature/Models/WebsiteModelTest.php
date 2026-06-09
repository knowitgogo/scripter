<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\WebsiteStatus;
use App\Models\User;
use App\Models\Website;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function website_receives_uuid_on_creation(): void
    {
        $website = Website::factory()->create();

        $this->assertNotEmpty($website->uuid);
        $this->assertTrue(UuidGenerator::isValid($website->uuid));
    }

    #[Test]
    public function website_internal_id_and_user_id_are_not_exposed_in_array(): void
    {
        $website = Website::factory()->create();

        $array = $website->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('user_id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function website_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new Website)->getRouteKeyName());
    }

    #[Test]
    public function website_can_be_resolved_by_uuid_route_key(): void
    {
        $website = Website::factory()->create();

        $resolved = (new Website)->resolveRouteBinding($website->uuid);

        $this->assertTrue($website->is($resolved));
    }

    #[Test]
    public function website_status_is_cast_to_enum(): void
    {
        $website = Website::factory()->suspended()->create();

        $this->assertSame(WebsiteStatus::Suspended, $website->status);
        $this->assertSame('suspended', $website->toArray()['status']);
    }

    #[Test]
    public function website_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $website = Website::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->is($website->user));
    }

    #[Test]
    public function user_has_many_websites(): void
    {
        $user = User::factory()->create();
        Website::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->websites);
    }

    #[Test]
    public function websites_table_persists_domain_fields(): void
    {
        $website = Website::factory()->create([
            'name' => 'Acme Site',
            'url' => 'https://acme.example.com',
        ]);

        $this->assertDatabaseHas('websites', [
            'uuid' => $website->uuid,
            'name' => 'Acme Site',
            'url' => 'https://acme.example.com',
            'status' => WebsiteStatus::Active->value,
        ]);
    }
}
