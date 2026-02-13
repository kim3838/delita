<?php

namespace App\Http\Requests\SalaryStatementModule;

use App\Enums\RegexValidation;
use App\Models\SalaryStatementModule;
use Illuminate\Validation\Rule;

class StoreSalaryStatementModuleRequest extends BaseSalaryStatementModuleRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SalaryStatementModule::class);
    }

    public function rules(): array
    {
        return array_merge([
            'key' => [
                'required',
                'string',
                'regex:' . RegexValidation::NO_WHITESPACE->value,
                'max:255',
                Rule::unique('salary_statement_modules')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],
        ], parent::rules());
    }
}
