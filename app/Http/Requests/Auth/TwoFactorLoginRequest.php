<?php

namespace App\Http\Requests\Auth;

use App\Blueprint\Auth\TwoFactorAuthenticationProvider;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class TwoFactorLoginRequest extends FormRequest
{
    protected $challengedUser;

    protected $remember;

    public function hasChallengedUser()
    {
        if ($this->challengedUser) {
            return true;
        }

        return $this->session()->has('login.id') && $this->challengedUser();
    }

    public function challengedUser()
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }

        $model = Auth::getProvider()->getModel();

        return $this->challengedUser = ($model::query()->find($this->session()->get('login.id')));
    }

    public function remember()
    {
        if (!$this->remember) {
            $this->remember = $this->session()->pull('login.remember', false);
        }

        return $this->remember;
    }

    public function hasValidCode()
    {
        return $this->input('code') && tap(App::make(TwoFactorAuthenticationProvider::class)->verify(
            decrypt($this->challengedUser()->two_factor_secret),
            $this->code
        ), function ($codeValid) {

            if ($codeValid) {

                $this->session()->forget('login.id');
            }
        });
    }

    public function hasValidRecoveryCode()
    {
        if (! $this->input('recovery_code')) {
            return;
        }

        return tap(collect($this->challengedUser()->recoveryCodes())->first(function ($code) {

            return hash_equals($code, $this->input('recovery_code')) ? $code : null;

        }), function ($recoveryCodeValid) {

            if ($recoveryCodeValid) {

                $this->session()->forget('login.id');
            }
        });
    }
}
