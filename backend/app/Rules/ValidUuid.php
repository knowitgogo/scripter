<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\UuidGenerator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a value is a well-formed UUID v4 string.
 */
final class ValidUuid implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! UuidGenerator::isValid($value)) {
            $fail('The :attribute must be a valid UUID.');
        }
    }
}
