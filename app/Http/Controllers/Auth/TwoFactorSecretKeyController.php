<?php

namespace App\Http\Controllers\Auth;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TwoFactorSecretKeyController extends Controller
{
    public function show(Request $request)
    {
        if (is_null($request->user()->two_factor_secret)) {
            return ResponseJson::notFoundResponse('Two factor authentication has not been enabled.');
        }

        return ResponseJson::successfulResponse([
            'secret_key' => decrypt($request->user()->two_factor_secret)
        ]);
    }
}
