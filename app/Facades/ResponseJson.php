<?php

namespace App\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static JsonResponse notFoundResponse(string $message = 'Not found.')
 * @method static JsonResponse successfulResponse(array $data = [], string $message = 'Success.')
 * @method static JsonResponse serverErrorResponse(array $data = [], string $message = 'Server error.')
 * @method static JsonResponse validationErrorResponse(array $errors = [], string $message = 'Validation error.')
 * @method static JsonResponse unauthorizedResponse(string $message = 'Unauthorized.')
 * @method static JsonResponse notAcceptableResponse(string $message = 'Not Acceptable.')
 * @method static JsonResponse methodNotAllowedResponse(string $message = 'Method not allowed.')
 * @method static JsonResponse unprocessableResponse(array $errors = [], string $message = 'Unprocessable entity.')
 * @method static JsonResponse forbiddenResponse(string $message = 'Forbidden.')
 * @method static JsonResponse sessionExpired(string $message = 'Session expired.')
 * @method static JsonResponse serviceUnavailableResponse(string $message = 'Service unavailable.')
 * @method static JsonResponse tooManyRequestsResponse(string $message = 'Too many attempts.')
 * @method static JsonResponse responseByCode($code)
 */
class ResponseJson extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'response_json';
    }
}
