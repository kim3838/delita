<?php

namespace App\Http\Controllers\Auth;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user->hasVerifiedEmail()) {
            if($request->expectsJson()){
                return ResponseJson::successfulResponse([], __('auth.email.already.verified'));
            } else {
                return $this->resolveRedirect();
            }
        }

        if ($request->user->markEmailAsVerified()) {
            event(new Verified($request->user));

            if($request->expectsJson()){
                return ResponseJson::successfulResponse([], __('auth.email.verified'));
            } else {
                return $this->resolveRedirect();
            }
        }

        if($request->expectsJson()){
            return ResponseJson::serverErrorResponse([], __('auth.email.verification.error'));
        } else {
            return __('auth.email.verification.error');
        }
    }

    private function resolveRedirect()
    {
        return redirect()->intended(config('app.frontend_url'));
    }
}
