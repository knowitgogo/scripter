<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\AssignWidgetTemplateDTO;
use App\DTOs\Widget\WidgetTemplateDTO;
use App\DTOs\Widget\WidgetTemplatesDTO;
use App\Enums\AuditAction;
use App\Events\Audit\GenericAuditEvent;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Models\WidgetTemplate;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use App\Repositories\Contracts\WidgetTemplateRepositoryInterface;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Assigns embed and hosted templates to marketplace widgets.
 */
final class WidgetTemplateAssignmentService
{
    public function __construct(
        private readonly WidgetRepositoryInterface $widgets,
        private readonly WidgetTemplateRepositoryInterface $widgetTemplates,
        private readonly AuditDispatcher $auditDispatcher,
    ) {}

    /**
     * @throws ModelNotFoundException
     * @throws DomainException
     */
    public function assign(string $widgetUuid, AssignWidgetTemplateDTO $payload, User $user): WidgetTemplatesDTO
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);

        if ($this->widgetTemplates->slugExistsForWidget($widget->id, $payload->slug)) {
            throw new DomainException('The slug has already been taken for this widget.', 422);
        }

        if ($payload->is_default) {
            $this->widgetTemplates->clearDefaultForWidget($widget->id);
        }

        /** @var WidgetTemplate $template */
        $template = $this->widgetTemplates->create([
            'widget_id' => $widget->id,
            'name' => $payload->name,
            'slug' => $payload->slug,
            'description' => $payload->description,
            'content' => $payload->content,
            'is_default' => $payload->is_default,
        ]);

        $template->load('widget');

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Created,
                subjectType: 'widget_template',
                subjectUuid: $template->uuid,
                actorUuid: $user->uuid,
                metadata: [
                    'widget_uuid' => $widget->uuid,
                    'slug' => $template->slug,
                    'is_default' => $template->is_default,
                ],
            ),
        );

        return $this->templatesForWidget($widget->uuid, $widget->id);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function assignDefault(string $widgetUuid, string $templateUuid, User $user): WidgetTemplateDTO
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);
        $template = $this->widgetTemplates->findByUuidForWidgetOrFail($widget->id, $templateUuid);

        if ($template->is_default) {
            return WidgetTemplateDTO::fromModel($template);
        }

        $this->widgetTemplates->clearDefaultForWidget($widget->id);
        $this->widgetTemplates->update($template, ['is_default' => true]);
        $template->refresh();
        $template->load('widget');

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Updated,
                subjectType: 'widget_template',
                subjectUuid: $template->uuid,
                actorUuid: $user->uuid,
                metadata: [
                    'widget_uuid' => $widget->uuid,
                    'slug' => $template->slug,
                    'is_default' => true,
                ],
            ),
        );

        return WidgetTemplateDTO::fromModel($template);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function unassign(string $widgetUuid, string $templateUuid, User $user): WidgetTemplatesDTO
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);
        $template = $this->widgetTemplates->findByUuidForWidgetOrFail($widget->id, $templateUuid);

        $this->widgetTemplates->delete($template);

        $this->auditDispatcher->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Deleted,
                subjectType: 'widget_template',
                subjectUuid: $template->uuid,
                actorUuid: $user->uuid,
                metadata: [
                    'widget_uuid' => $widget->uuid,
                    'slug' => $template->slug,
                ],
            ),
        );

        return $this->templatesForWidget($widget->uuid, $widget->id);
    }

    private function templatesForWidget(string $widgetUuid, int $widgetId): WidgetTemplatesDTO
    {
        $templates = $this->widgetTemplates->listForWidget($widgetId)
            ->map(fn (WidgetTemplate $template): WidgetTemplateDTO => WidgetTemplateDTO::fromModel($template))
            ->values()
            ->all();

        return WidgetTemplatesDTO::forWidget($widgetUuid, $templates);
    }
}
