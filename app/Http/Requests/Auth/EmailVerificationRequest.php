<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class EmailVerificationRequest extends FormRequest
{
    public function __construct(
        public $user = null
    ) {}

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->user = User::find(Crypt::decrypt((string) $this->route('id')));

        if(!$this->user){
            return false;
        }

        if (! hash_equals(
            $this->user->getEmailForVerification(),
            Crypt::decrypt((string) $this->route('hash'))
        )) {
            return false;
        }

        return true;
    }
}
