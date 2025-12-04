<?php

namespace App\Http\Requests\Account;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = Account::query()->findOrfail($this->route('accountId'));

        return $this->user()->can('update', $account);
    }

    public function rules(): array
    {
        return [
            'plan' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'plan.required' => 'Account plan is required',
        ];
    }
}
