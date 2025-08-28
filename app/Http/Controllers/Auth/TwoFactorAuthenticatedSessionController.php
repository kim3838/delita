<?php

namespace App\Http\Controllers\Auth;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthenticatedSessionController extends Controller
{
    public function store(TwoFactorLoginRequest $request): JsonResponse
    {
        $user = $request->challengedUser();

        if (!$user) {
            return ResponseJson::notFoundResponse('Challenged user not found.');
        }

        if ($code = $request->hasValidRecoveryCode()) {

            $user->replaceRecoveryCode($code);

        } else if (!$request->hasValidCode()) {

            return ResponseJson::validationErrorResponse([], 'Invalid code');
        }

        Auth::login($user, $remember = $request->remember());

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return ResponseJson::successfulResponse();
    }
}
