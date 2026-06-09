<?php

declare(strict_types=1);

namespace App\Http\Requests\Tag;

use App\Http\Requests\ApiRequest;

/**
 * Validates tag creation input.
 */
final class CreateTagRequest extends ApiRequest
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
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tags,slug'],
        ];
    }
}
