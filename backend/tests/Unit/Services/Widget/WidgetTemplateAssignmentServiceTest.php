<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Widget;

use App\DTOs\Widget\AssignWidgetTemplateDTO;
use App\Enums\AuditAction;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Models\Widget;
use App\Models\WidgetTemplate;
use App\Repositories\Eloquent\EloquentWidgetRepository;
use App\Repositories\Eloquent\EloquentWidgetTemplateRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Widget\WidgetTemplateAssignmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetTemplateAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private WidgetTemplateAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        $this->service = new WidgetTemplateAssignmentService(
            new EloquentWidgetRepository,
            new EloquentWidgetTemplateRepository,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_assigns_template_to_widget(): void
    {
        $widget = Widget::factory()->create();
        $user = User::factory()->create();

        $result = $this->service->assign(
            $widget->uuid,
            new AssignWidgetTemplateDTO(
                name: 'Embedded Script',
                slug: 'embedded',
                content: '<script src="{{cdn_url}}"></script>',
                description: 'Embed snippet.',
                is_default: true,
            ),
            $user,
        );

        $this->assertSame($widget->uuid, $result->widget_uuid);
        $this->assertCount(1, $result->templates);
        $this->assertSame('embedded', $result->templates[0]->slug);
        $this->assertTrue($result->templates[0]->is_default);
        $this->assertDatabaseHas('widget_templates', [
            'widget_id' => $widget->id,
            'slug' => 'embedded',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_type' => 'widget_template',
            'actor_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function it_clears_existing_default_when_assigning_new_default_template(): void
    {
        $widget = Widget::factory()->create();
        $user = User::factory()->create();
        WidgetTemplate::factory()->for($widget)->embedded()->defaultTemplate()->create();

        $result = $this->service->assign(
            $widget->uuid,
            new AssignWidgetTemplateDTO(
                name: 'Hosted Iframe',
                slug: 'hosted',
                content: '<iframe></iframe>',
                is_default: true,
            ),
            $user,
        );

        $this->assertSame('hosted', $result->templates[0]->slug);
        $this->assertTrue($result->templates[0]->is_default);
        $this->assertDatabaseHas('widget_templates', [
            'widget_id' => $widget->id,
            'slug' => 'embedded',
            'is_default' => false,
        ]);
        $this->assertDatabaseHas('widget_templates', [
            'widget_id' => $widget->id,
            'slug' => 'hosted',
            'is_default' => true,
        ]);
    }

    #[Test]
    public function it_assigns_default_template_for_widget(): void
    {
        $widget = Widget::factory()->create();
        $user = User::factory()->create();
        $embedded = WidgetTemplate::factory()->for($widget)->embedded()->defaultTemplate()->create();
        $hosted = WidgetTemplate::factory()->for($widget)->hosted()->create();

        $result = $this->service->assignDefault($widget->uuid, $hosted->uuid, $user);

        $this->assertSame('hosted', $result->slug);
        $this->assertTrue($result->is_default);
        $this->assertFalse($embedded->fresh()->is_default);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_type' => 'widget_template',
            'subject_uuid' => $hosted->uuid,
        ]);
    }

    #[Test]
    public function it_unassigns_template_from_widget(): void
    {
        $widget = Widget::factory()->create();
        $user = User::factory()->create();
        $template = WidgetTemplate::factory()->for($widget)->embedded()->create();
        WidgetTemplate::factory()->for($widget)->hosted()->create();

        $result = $this->service->unassign($widget->uuid, $template->uuid, $user);

        $this->assertCount(1, $result->templates);
        $this->assertSame('hosted', $result->templates[0]->slug);
        $this->assertDatabaseMissing('widget_templates', ['uuid' => $template->uuid]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Deleted->value,
            'subject_type' => 'widget_template',
            'subject_uuid' => $template->uuid,
        ]);
    }

    #[Test]
    public function it_throws_when_template_slug_is_already_taken_for_widget(): void
    {
        $widget = Widget::factory()->create();
        $user = User::factory()->create();
        WidgetTemplate::factory()->for($widget)->embedded()->create();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The slug has already been taken for this widget.');

        $this->service->assign(
            $widget->uuid,
            new AssignWidgetTemplateDTO(
                name: 'Duplicate',
                slug: 'embedded',
                content: '<script></script>',
            ),
            $user,
        );
    }

    #[Test]
    public function it_throws_when_template_does_not_belong_to_widget(): void
    {
        $widget = Widget::factory()->create();
        $otherWidget = Widget::factory()->create();
        $user = User::factory()->create();
        $template = WidgetTemplate::factory()->for($otherWidget)->embedded()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->unassign($widget->uuid, $template->uuid, $user);
    }

    #[Test]
    public function it_throws_when_widget_is_not_found_for_assignment(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->assign(
            '00000000-0000-0000-0000-000000000000',
            new AssignWidgetTemplateDTO(
                name: 'Embedded Script',
                slug: 'embedded',
                content: '<script></script>',
            ),
            $user,
        );
    }
}
