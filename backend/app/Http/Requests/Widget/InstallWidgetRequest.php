<?php

declare(strict_types=1);

namespace App\Http\Requests\Widget;

use App\Http\Requests\ApiRequest;
use App\Rules\ValidUuid;
use Illuminate\Validation\Rule;

/**
 * Validates widget installation on a customer website.
 */
final class InstallWidgetRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|ValidUuid|Rule>>
     */
    public function rules(): array
    {
        return [
            'website_uuid' => ['required', new ValidUuid(), Rule::exists('websites', 'uuid')],
            'widget_version_uuid' => ['required', new ValidUuid(), Rule::exists('widget_versions', 'uuid')],
            'configuration' => ['sometimes', 'array'],
        ];
    }
}
