<?php

namespace App\Http\Requests\Company;

use App\Models\Company;

class StoreCompanyRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Company::class);
    }
}
