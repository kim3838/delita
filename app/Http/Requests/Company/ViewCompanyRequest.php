<?php

namespace App\Http\Requests\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class ViewCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = Company::query()->where('ulid', $this->route('ulid'))->firstOrfail();

        return $this->user()->can('view', $company);
    }
}
