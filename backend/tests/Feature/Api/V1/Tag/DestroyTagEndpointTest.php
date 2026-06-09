<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Tag;

use App\Enums\AuditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithTags;
use Tests\TestCase;

final class DestroyTagEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithTags, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function destroy_endpoint_deletes_tag(): void
    {
        $user = $this->createAuthUser();
        $tag = $this->createTagFor($user, ['slug' => 'marketing']);

        $this->actingAsJwt($user)
            ->deleteJson('/api/v1/tags/'.$tag->uuid)
            ->assertNoContent();

        $this->assertDatabaseMissing('tags', ['uuid' => $tag->uuid]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deleted->value,
            'subject_uuid' => $tag->uuid,
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function destroy_endpoint_returns_404_for_unknown_uuid(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->deleteJson('/api/v1/tags/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }
}
