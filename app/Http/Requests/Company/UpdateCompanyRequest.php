<?php

namespace App\Http\Requests\Company;

use App\Models\Company;

class UpdateCompanyRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        $company = Company::query()->findOrfail($this->route('companyId'));

        return $this->user()->can('update', $company);
    }
}
