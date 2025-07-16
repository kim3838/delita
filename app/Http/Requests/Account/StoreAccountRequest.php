<?php

namespace App\Http\Requests\Account;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Account::class);
    }

    public function rules(): array
    {
        return [
            'number' => 'required|unique:accounts',
            'type' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Account number is required',
            'number.unique' => 'Account number has already been taken',
            'type.required' => 'Account type is required',
        ];
    }
}
