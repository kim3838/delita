<?php

namespace App\Http\Requests\Account;

use App\Models\Account;

class StoreAccountRequest extends BaseAccountRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Account::class);
    }
}
