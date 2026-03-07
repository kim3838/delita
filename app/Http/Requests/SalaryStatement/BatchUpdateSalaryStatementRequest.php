<?php

namespace App\Http\Requests\SalaryStatement;

use App\Models\SalaryStatement;
use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateSalaryStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchUpdate', SalaryStatement::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer',

            'keep_is_paid' => 'required|boolean',
            'is_paid' => 'nullable|boolean',

            'salary_statement_identifiers' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'keep_is_paid.required' => 'Keep is paid flag is required',

            'salary_statement_identifiers.required' => 'Salary statements are required'
        ];
    }
}
