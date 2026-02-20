<?php

namespace App\Http\Requests\SalaryStatement;

use App\Models\SalaryStatement;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroySalaryStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', SalaryStatement::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'salary_statement_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'salary_statement_ids.required' => 'Salary statement ids is required',
            'salary_statement_ids.array' => 'Salary statement ids must be an array',
        ];
    }
}
