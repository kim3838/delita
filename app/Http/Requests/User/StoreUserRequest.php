<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                'max:255',
                Rule::unique('users')
            ],
            'email' => [
                'required',
                'email:rfc',
                Rule::unique('users')
            ],
            'password' => [
                'required',
                'confirmed',
                \Illuminate\Validation\Rules\Password::defaults()
            ],
            'status' => 'required|numeric',
            'timezone' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Username is required',
        ];
    }
}
