<?php

declare(strict_types=1);

namespace Tests\Feature\Tag;

use App\Enums\AuditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithTags;
use Tests\TestCase;

/**
 * End-to-end tag CRUD lifecycle integration tests.
 */
final class TagCrudFlowTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithTags, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function full_crud_lifecycle_create_list_show_update_delete(): void
    {
        $user = $this->createAuthUser();

        $createResponse = $this->actingAsJwt($user)->postJson(
            '/api/v1/tags',
            $this->tagPayload([
                'name' => 'Flow Tag',
                'slug' => 'flow-tag',
            ]),
        );

        $createResponse->assertCreated();
        $uuid = (string) $createResponse->json('data.uuid');
        $this->assertNotEmpty($uuid);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/tags')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $uuid);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/tags/'.$uuid)
            ->assertOk()
            ->assertJsonPath('data.name', 'Flow Tag');

        $this->actingAsJwt($user)
            ->putJson('/api/v1/tags/'.$uuid, [
                'name' => 'Flow Tag Updated',
                'slug' => 'flow-tag-updated',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Flow Tag Updated');

        $this->actingAsJwt($user)
            ->deleteJson('/api/v1/tags/'.$uuid)
            ->assertNoContent();

        $this->assertDatabaseMissing('tags', ['uuid' => $uuid]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_uuid' => $uuid,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_uuid' => $uuid,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deleted->value,
            'subject_uuid' => $uuid,
        ]);
    }
}
