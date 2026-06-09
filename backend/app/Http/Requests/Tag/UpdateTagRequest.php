<?php

declare(strict_types=1);

namespace App\Http\Requests\Tag;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Validates tag update input.
 */
final class UpdateTagRequest extends ApiRequest
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
        $tagUuid = (string) $this->route('tag');

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tags', 'slug')->ignore($tagUuid, 'uuid'),
            ],
        ];
    }
}
