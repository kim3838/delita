<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ConfirmTwoFactorAuthentication;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;

class ConfirmedTwoFactorAuthenticationController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function store(Request $request, ConfirmTwoFactorAuthentication $confirm): JsonResponse
    {
        $confirm($request->user(), $request->input('code'));

        return ResponseJson::successfulResponse([], Lang::get('auth.two-factor.confirmed'));
    }
}
