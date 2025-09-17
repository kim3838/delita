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

            'formulable_type' => 'required|numeric|integer',

            'amount' => 'sometimes|required|numeric|min:1|regex:/^\d{1,12}(\.\d{1,6})?$/',
            'currency' => 'sometimes|string',
            'pay_period' => 'sometimes|required|numeric|integer',
            'pay_type' => 'sometimes|required|numeric|integer',
            'pay_frequency_id' => 'sometimes|required|numeric|integer',

            'amountable_start' => 'sometimes|required|numeric|integer',
            'amountable_end' => 'sometimes|required|numeric|integer',

            'start_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],
            'end_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payroll_componentable_id.required' => 'Payroll component is required',
            'amount.required' => 'Amount is required',
            'amount.regex' => 'Amount must be a valid number with up to 12 digits and maximum 6 decimal places',
            'pay_period.required' => 'Pay period is required',
            'pay_type.required' => 'Pay type is required',
            'pay_frequency_id.required' => 'Pay frequency is required',
            'amountable_start.required' => 'Date start is required',
            'start_date.date_format' => 'Start date must match the format Y-m-d (2000-01-01)',
            'amountable_end.required' => 'Date end is required',
            'end_date.date_format' => 'End date must match the format Y-m-d (2000-01-01)',
            'end_date.after_or_equal' => 'End date must be equal to or after the start date',
        ];
    }
}
