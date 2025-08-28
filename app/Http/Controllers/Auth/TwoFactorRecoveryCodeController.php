<?php

namespace App\Http\Controllers\Auth;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorRecoveryCodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->two_factor_secret ||
            ! $request->user()->two_factor_recovery_codes) {
            return ResponseJson::successfulResponse();
        }

        return ResponseJson::successfulResponse([
            'recovery_codes' => json_decode(decrypt($request->user()->two_factor_recovery_codes), true)
        ]);
    }
}
