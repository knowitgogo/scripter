<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Support\ApiResponse;

/**
 * Base form request with API envelope validation errors.
 */
abstract class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                message: 'Validation failed.',
                errors: $validator->errors()->all(),
                status: 422,
            ),
        );
    }
}
