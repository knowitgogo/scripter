<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Widget;

use App\Models\Widget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\Concerns\InteractsWithWidgets;
use Tests\TestCase;

final class ListWidgetsEndpointTest extends TestCase
{
    use InteractsWithAuthentication, InteractsWithWidgets, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function list_endpoint_returns_published_widgets_ordered_by_name(): void
    {
        $user = $this->createAuthUser();
        Widget::factory()->published()->create(['name' => 'Zulu Widget', 'slug' => 'zulu-widget']);
        Widget::factory()->published()->create(['name' => 'Alpha Widget', 'slug' => 'alpha-widget']);
        Widget::factory()->draft()->create(['name' => 'Hidden Widget', 'slug' => 'hidden-widget']);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/widgets');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'name', 'slug', 'status', 'description', 'created_at', 'updated_at'],
            ],
        ]);
        $this->assertSame('Alpha Widget', $response->json('data.0.name'));
        $this->assertSame('Zulu Widget', $response->json('data.1.name'));
        $this->assertArrayNotHasKey('id', $response->json('data.0'));
    }

    #[Test]
    public function list_endpoint_filters_widgets_by_search_query(): void
    {
        $user = $this->createAuthUser();
        Widget::factory()->published()->create(['name' => 'Feedback Form', 'slug' => 'feedback-form']);
        Widget::factory()->published()->create(['name' => 'Newsletter Signup', 'slug' => 'newsletter-signup']);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/widgets?search=feedback');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.slug', 'feedback-form');
    }

    #[Test]
    public function list_endpoint_filters_widgets_by_category_query(): void
    {
        $user = $this->createAuthUser();
        Widget::factory()->published()->create(['name' => 'Feedback Form', 'slug' => 'feedback-form']);
        Widget::factory()->published()->create(['name' => 'Feedback Popup', 'slug' => 'feedback-popup']);
        Widget::factory()->published()->create(['name' => 'Newsletter', 'slug' => 'newsletter-signup']);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/widgets?category=feedback');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function list_endpoint_filters_widgets_by_slug_list(): void
    {
        $user = $this->createAuthUser();
        Widget::factory()->published()->create(['slug' => 'feedback-form']);
        Widget::factory()->published()->create(['slug' => 'newsletter-signup']);
        Widget::factory()->published()->create(['slug' => 'analytics-dashboard']);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/widgets?slugs[]=feedback-form&slugs[]=analytics-dashboard');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function list_endpoint_searches_widget_descriptions(): void
    {
        $user = $this->createAuthUser();
        Widget::factory()->published()->create([
            'name' => 'Survey Tool',
            'slug' => 'survey-tool',
            'description' => 'Collect on-page feedback with themes.',
        ]);
        Widget::factory()->published()->create(['slug' => 'newsletter-signup']);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/widgets?search=on-page');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.slug', 'survey-tool');
    }

    #[Test]
    public function list_endpoint_returns_empty_collection_when_no_published_widgets_exist(): void
    {
        $user = $this->createAuthUser();
        Widget::factory()->draft()->create(['slug' => 'draft-only']);

        $response = $this->actingAsJwt($user)->getJson('/api/v1/widgets');

        $this->assertSuccessfulApiEnvelope($response);
        $response->assertJsonCount(0, 'data');
    }

    #[Test]
    public function list_endpoint_returns_422_for_invalid_search_query(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->getJson('/api/v1/widgets?search='.str_repeat('a', 101))
            ->assertUnprocessable();
    }
}
