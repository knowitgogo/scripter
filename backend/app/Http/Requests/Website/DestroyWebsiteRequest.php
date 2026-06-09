<?php

declare(strict_types=1);

namespace App\Http\Requests\Website;

use App\Http\Requests\ApiRequest;

/**
 * Authorizes deleting a website owned by the authenticated user.
 */
final class DestroyWebsiteRequest extends ApiRequest
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
