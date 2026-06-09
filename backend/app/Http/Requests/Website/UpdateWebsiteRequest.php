<?php

declare(strict_types=1);

namespace App\Http\Requests\Website;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Validates website update input.
 */
final class UpdateWebsiteRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<\Illuminate\Validation\Rules\Unique|string>>
     */
    public function rules(): array
    {
        $websiteUuid = (string) $this->route('website');

        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => [
                'required',
                'string',
                'url',
                'max:2048',
                Rule::unique('websites', 'url')->ignore($websiteUuid, 'uuid'),
            ],
        ];
    }
}
