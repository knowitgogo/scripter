<?php

declare(strict_types=1);

namespace App\Http\Requests\Website;

use App\Http\Requests\ApiRequest;

/**
 * Validates website creation input.
 */
final class CreateWebsiteRequest extends ApiRequest
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
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'url', 'max:2048', 'unique:websites,url'],
        ];
    }
}
