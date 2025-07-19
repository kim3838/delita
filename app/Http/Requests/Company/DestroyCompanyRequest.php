<?php

namespace App\Http\Requests\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class DestroyCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = Company::findOrfail($this->route('companyId'));

        return $this->user()->can('delete', $company);
    }
}
