<?php

declare(strict_types=1);

namespace App\Http\Requests\Widget;

use App\Http\Requests\ApiRequest;

/**
 * Authorizes listing published widgets in the marketplace catalog.
 */
final class ListWidgetsRequest extends ApiRequest
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
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slugs' => ['sometimes', 'array'],
            'slugs.*' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ];
    }
}
