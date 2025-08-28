<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\DisableTwoFactorAuthentication;
use App\Actions\Auth\EnableTwoFactorAuthentication;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends Controller
{
    public function store(Request $request, EnableTwoFactorAuthentication $enableTwoFactorAuthentication): JsonResponse
    {
        $enableTwoFactorAuthentication($request->user());

        return ResponseJson::successfulResponse();
    }

    public function destroy(Request $request, DisableTwoFactorAuthentication $disableTwoFactorAuthentication): JsonResponse
    {
        $disableTwoFactorAuthentication($request->user());

        return ResponseJson::successfulResponse();
    }
}
