<?php

declare(strict_types=1);

namespace App\Http\Requests\Widget;

use App\Http\Requests\ApiRequest;

/**
 * Validates admin widget activation input.
 */
final class ActivateWidgetRequest extends ApiRequest
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
