<?php

namespace App\Http\Requests\SalaryStatementModule;

use Illuminate\Foundation\Http\FormRequest;

class BaseSalaryStatementModuleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer',
            'name' => 'required|string|max:255',
            'formulable_type' => 'required|numeric|integer',
            'aggregation' => 'required|boolean',
            'property' => 'required|string|max:255',
            'attribute' => 'required|string|max:255',
            'conditions' => 'nullable|string',
        ];
    }
}
