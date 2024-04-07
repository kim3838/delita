<?php

namespace App\Concrete;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonResponseScaffolder
{
    protected function response($successful, $data, $message, $errors = [], $responseCode = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'successful' => $successful,
            'code' => $responseCode,
            'message' => $message,
            'values' => $data,
            'errors' => $errors
        ], $responseCode
        );
    }

    public function notFoundResponse($message = 'Not found.'): JsonResponse
    {
        return $this->response(false, [], $message, [], Response::HTTP_NOT_FOUND);
    }

    public function successfulResponse($data = null, $message = 'Success.'): JsonResponse
    {
        return $this->response(true, $data, $message);
    }

    public function serverErrorResponse($errors = [], $message = 'Server error.'): JsonResponse
    {
        return $this->response(false, [], $message, $errors, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function validationErrorResponse($errors = [], $message = 'Validation error.'): JsonResponse
    {
        return $this->response(false, [], $message, $errors, Response::HTTP_BAD_REQUEST);
    }

    public function unauthorizedResponse($message = 'Unauthorized.'): JsonResponse
    {
        return $this->response(false, [], $message, [], Response::HTTP_UNAUTHORIZED);
    }

    public function notAcceptableResponse($message = 'Not Acceptable'): JsonResponse
    {
        return $this->response(false, [], $message, [], Response::HTTP_NOT_ACCEPTABLE);
    }

    public function unprocessableResponse($errors = [], $message = 'Unprocessable entity.'): JsonResponse
    {
        return $this->response(false, [], $message, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function forbiddenResponse($message = 'Forbidden.'): JsonResponse
    {
        return $this->response(false, [], $message, [], Response::HTTP_FORBIDDEN);
    }

    /**
     * Laravel Custom 419 code response
     * Page Expired Response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function sessionExpired($message = 'Session expired.'): JsonResponse
    {
        return $this->response(false, [], $message, [], 419);
    }

    public function serviceUnavailableResponse($message = 'Service unavailable.'): JsonResponse
    {
        return $this->response(false, [], $message, [], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function tooManyRequestsResponse($message = 'Too many attempts.'): JsonResponse
    {
        return $this->response(false, [], $message, [], Response::HTTP_TOO_MANY_REQUESTS);
    }

    public function responseByCode($code): JsonResponse
    {
        return match (true){
            $code == Response::HTTP_INTERNAL_SERVER_ERROR => $this->serverErrorResponse(),
            $code == Response::HTTP_SERVICE_UNAVAILABLE => $this->serviceUnavailableResponse(),
            $code == Response::HTTP_NOT_FOUND => $this->notFoundResponse(),
            $code == Response::HTTP_FORBIDDEN => $this->forbiddenResponse(),
            $code == Response::HTTP_TOO_MANY_REQUESTS => $this->tooManyRequestsResponse()
        };
    }
}
