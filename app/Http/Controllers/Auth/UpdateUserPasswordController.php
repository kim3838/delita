<?php

namespace App\Http\Controllers\Auth;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;

class UpdateUserPasswordController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info([
            'method' => get_class() . '@' . __FUNCTION__,
            'line' => __LINE__,
            'session' => collect(Session::all())->except(['_previous', '_flash'])->all(),
        ]);

        $request->validate([
            'current_password' => ['required', 'string', 'current_password:web'],
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        \Log::debug(print_r([
            'Auth Default Driver' => Auth::getDefaultDriver(),
            'Config Default Guard' => config('auth.defaults.guard'),
            'BEFORE updatePassword: session password_hash_web' => $request->session()->get('password_hash_web'),
            'BEFORE updatePassword: session password_hash_sanctum' => $request->session()->get('password_hash_sanctum'),
            'BEFORE updatePassword: user password_hash' => $request->user() ? $request->user()->getAuthPassword() : ''
        ], true));

        auth()->user()->forceFill([
            'password' => Hash::make($request->new_password),
        ])->save();

        \Log::debug(print_r([
            'Auth Default Driver' => Auth::getDefaultDriver(),
            'Config Default Guard' => config('auth.defaults.guard'),
            'updatePassword AFTER session password_hash_web' => $request->session()->get('password_hash_web'),
            'updatePassword AFTER session password_hash_sanctum' => $request->session()->get('password_hash_sanctum'),
            'updatePassword AFTER user password_hash' => $request->user() ? $request->user()->getAuthPassword() : ''
        ], true));

        return ResponseJson::successfulResponse();
    }
}
