<?php

namespace App\Http\Requests\Account;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class ViewAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = Account::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        return $this->user()->can('view', $account);
    }
}
