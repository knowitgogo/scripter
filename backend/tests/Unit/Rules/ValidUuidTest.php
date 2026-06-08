<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\ValidUuid;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ValidUuidTest extends TestCase
{
    #[Test]
    public function it_passes_for_valid_uuid(): void
    {
        $validator = Validator::make(
            ['uuid' => '550e8400-e29b-41d4-a716-446655440000'],
            ['uuid' => [new ValidUuid]],
        );

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function it_fails_for_invalid_uuid(): void
    {
        $validator = Validator::make(
            ['uuid' => 'not-a-uuid'],
            ['uuid' => [new ValidUuid]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            ['The uuid must be a valid UUID.'],
            $validator->errors()->get('uuid'),
        );
    }
}
