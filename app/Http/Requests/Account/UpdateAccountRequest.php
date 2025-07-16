<?php

namespace App\Http\Requests\Account;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = Account::findOrfail($this->route('accountId'));

        return $this->user()->can('update', $account);
    }

    public function rules(): array
    {
        return [
            'type' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Account type is required',
        ];
    }
}
