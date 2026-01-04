<?php

namespace App\Http\Requests\Account;

use App\Models\Account;

class UpdateAccountRequest extends BaseAccountRequest
{
    public function authorize(): bool
    {
        $account = Account::query()->findOrfail($this->route('accountId'));

        return $this->user()->can('update', $account);
    }
}
