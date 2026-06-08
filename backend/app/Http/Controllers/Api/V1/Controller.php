<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;

/**
 * Base controller for versioned API endpoints.
 *
 * Controllers must remain thin: validate, delegate to Services, return ApiResponse.
 */
abstract class Controller extends BaseController
{
}
