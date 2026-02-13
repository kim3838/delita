<?php

namespace App\Http\Requests\SalaryStatementModule;

use App\Enums\RegexValidation;
use App\Models\SalaryStatementModule;
use Illuminate\Validation\Rule;

class UpdateSalaryStatementModuleRequest extends BaseSalaryStatementModuleRequest
{
    public function authorize(): bool
    {
        $salaryStatementModule = SalaryStatementModule::query()->findOrfail($this->route('salaryStatementModuleId'));

        return $this->user()->can('update', $salaryStatementModule);
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
                    return $query->where('company_id', $this->input('company_id'))
                        ->whereNot('id', $this->route('salaryStatementModuleId'));
                })
            ]
        ], parent::rules());
    }
}
