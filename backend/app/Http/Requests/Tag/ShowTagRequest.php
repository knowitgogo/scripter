<?php

declare(strict_types=1);

namespace App\Http\Requests\Tag;

use App\Http\Requests\ApiRequest;

/**
 * Authorizes viewing a single tag.
 */
final class ShowTagRequest extends ApiRequest
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
