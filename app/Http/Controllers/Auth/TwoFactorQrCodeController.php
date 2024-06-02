<?php

namespace App\Http\Controllers\Auth;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TwoFactorQrCodeController extends Controller
{
    public function show(Request $request)
    {
        if (is_null($request->user()->two_factor_secret)) {
            return ResponseJson::successfulResponse();
        }

        return ResponseJson::successfulResponse([
            'svg' => $request->user()->twoFactorQrCodeSvg(),
            'url' => $request->user()->twoFactorQrCodeUrl(),
        ]);
    }
}
