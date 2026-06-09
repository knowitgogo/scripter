<?php

declare(strict_types=1);

namespace App\Http\Requests\Widget;

use App\Http\Requests\ApiRequest;

/**
 * Validates admin widget version deprecation input.
 */
final class DeprecateWidgetVersionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
