<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\WidgetTemplateDTO;
use App\Models\WidgetTemplate;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use App\Repositories\Contracts\WidgetTemplateRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves marketplace widget install and embed templates.
 */
final class WidgetTemplateService
{
    public function __construct(
        private readonly WidgetRepositoryInterface $widgets,
        private readonly WidgetTemplateRepositoryInterface $widgetTemplates,
    ) {}

    /**
     * @return list<WidgetTemplateDTO>
     *
     * @throws ModelNotFoundException
     */
    public function listForWidget(string $widgetUuid): array
    {
        $widgetId = $this->widgets->findByUuidOrFail($widgetUuid)->id;

        return $this->widgetTemplates->listForWidget($widgetId)
            ->map(fn (WidgetTemplate $template): WidgetTemplateDTO => WidgetTemplateDTO::fromModel($template))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): WidgetTemplateDTO
    {
        /** @var WidgetTemplate $template */
        $template = $this->widgetTemplates->findByUuidOrFail($uuid);
        $template->load('widget');

        return WidgetTemplateDTO::fromModel($template);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByWidgetAndSlug(string $widgetUuid, string $slug): WidgetTemplateDTO
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);
        $template = $this->widgetTemplates->findByWidgetAndSlug($widget->id, $slug);

        if ($template === null) {
            throw (new ModelNotFoundException)->setModel(WidgetTemplate::class);
        }

        return WidgetTemplateDTO::fromModel($template);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getDefaultForWidget(string $widgetUuid): WidgetTemplateDTO
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);
        $template = $this->widgetTemplates->findDefaultForWidget($widget->id);

        if ($template === null) {
            throw (new ModelNotFoundException)->setModel(WidgetTemplate::class);
        }

        return WidgetTemplateDTO::fromModel($template);
    }
}
