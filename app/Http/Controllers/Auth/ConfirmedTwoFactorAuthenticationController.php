<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ConfirmTwoFactorAuthentication;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfirmedTwoFactorAuthenticationController extends Controller
{
    public function store(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $confirm($request->user(), $request->input('code'));

        return ResponseJson::successfulResponse();
    }
}
