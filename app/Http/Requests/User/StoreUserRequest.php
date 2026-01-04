<?php

namespace App\Http\Requests\User;

use App\Enums\RegexValidation;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'regex:' . RegexValidation::NO_WHITESPACE->value,
                'max:255',
                Rule::unique('users')
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                ...(App::environment('production') ? [
                    Rule::unique('users')
                ] : [])
            ],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::defaults()
            ],
            'status' => 'required|numeric',
            'timezone' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Username is required',
            'name.regex' => 'Username must not contain spaces',
            'name.unique' => 'Username has already been taken',
            'email.required' => 'User email is required',
            'email.unique' => 'User email has already been taken',
            'password.required' => 'Password is required',
            'timezone.required' => 'User timezone is required',
            'password.confirmed' => 'Password confirmation does not match',
        ];
    }
}
