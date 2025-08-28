<?php

namespace App\Http\Controllers\Auth;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        ////Todo: Refactor limiter for email verification and password reset link request
        //Limit 1 request in 1 minute
        if(RateLimiter::tooManyAttempts($request->email, 1)){

            $availableIn = RateLimiter::availableIn($request->email);

            $rateLimitExceedsMessage = "Try again in $availableIn second" . ($availableIn > 0 ? 's' : '') . ".";

            return ResponseJson::tooManyRequestsResponse($rateLimitExceedsMessage);
        }

        RateLimiter::hit($request->email);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return ResponseJson::successfulResponse([], __($status));
    }
}
