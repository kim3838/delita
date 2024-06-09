<?php

namespace App\Http\Controllers\Auth;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            if($request->expectsJson()){
                return ResponseJson::successfulResponse([], __('auth.email.already.verified'));
            }
        }

        $emailForVerification = $request->user()->getEmailForVerification();

        //Todo: Refactor limiter for email verification and password reset link request
        //Limit 1 request in 1 minute
        if(RateLimiter::tooManyAttempts($emailForVerification, 1)){

            $availableIn = RateLimiter::availableIn($emailForVerification);

            $rateLimitExceedsMessage = "Try again in $availableIn second" . ($availableIn > 0 ? 's' : '') . ".";

            return ResponseJson::tooManyRequestsResponse($rateLimitExceedsMessage);
        }

        RateLimiter::hit($emailForVerification);

        $request->user()->sendEmailVerificationNotification();

        $verificationSentMessage = __(
            'auth.email.verification.sent', [
                'email' => $emailForVerification
            ]
        );

        return ResponseJson::successfulResponse([], $verificationSentMessage);
    }
}
