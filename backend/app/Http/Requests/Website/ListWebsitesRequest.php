<?php

declare(strict_types=1);

namespace App\Http\Requests\Website;

use App\Http\Requests\ApiRequest;
use App\Rules\ValidUuid;
use Illuminate\Validation\Rule;

/**
 * Authorizes listing websites for the authenticated user.
 */
final class ListWebsitesRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<\Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'tag_uuids' => ['sometimes', 'array'],
            'tag_uuids.*' => ['required', new ValidUuid(), Rule::exists('tags', 'uuid')],
        ];
    }
}
