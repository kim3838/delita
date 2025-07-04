<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BasePolymorphicEmployeePayrollComponentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => 'sometimes|required|numeric|integer',

            'payroll_componentable_id' => 'required|numeric|integer',
            'payroll_componentable_type' => 'required|string',

            'amount' => 'sometimes|required|numeric|min:1',
            'currency' => 'sometimes|nullable',
            'pay_period' => 'sometimes|required|numeric|integer',
            'pay_type' => 'sometimes|required|numeric|integer',
            'pay_frequency' => 'sometimes|required|numeric|integer',

            'start_date' => 'sometimes|nullable|date_format:Y-m-d',
            'end_date' => 'sometimes|nullable|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'payroll_componentable_id.required' => 'Payroll component is required',
            'amount.required' => 'Amount is required',
            'pay_period.required' => 'Pay period is required',
            'pay_type.required' => 'Pay type is required',
            'pay_frequency.required' => 'Pay frequency is required',
        ];
    }
}
