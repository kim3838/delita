<?php

namespace App\Http\Requests\Account;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class ListAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Account::class);
    }
}
