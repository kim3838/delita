<?php

namespace App\Http\Requests\Payroll;

use App\Enums\PayFrequency;
use App\Enums\SemiMonthlySequence;
use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BasePayrollGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Payroll::class);
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    public function baseRules(): array
    {
        return [
            'company_id' => 'required|numeric|integer|exists:companies,id',
            'year' => 'required|numeric|integer',
            'month' => 'required|numeric|integer',
            'pay_frequency' => [
                'required',
                'integer',
                Rule::in([
                    PayFrequency::WEEKLY,
                    PayFrequency::SEMIMONTHLY,
                    PayFrequency::MONTHLY
                ])
            ],
            'frequency_sequence' => [
                'nullable',
                'integer',
                Rule::in([
                    SemiMonthlySequence::FIRST_HALF,
                    SemiMonthlySequence::SECOND_HALF
                ])
            ],
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'remarks' => 'nullable|string|max:255',
            'employee_ids' => 'required|array|min:1',
        ];
    }

    public function messages(): array
    {
        return array_merge([
            'company_id.required' => 'Company is required',
            'year.required' => 'Year is required',
            'month.required' => 'Month is required',

            'pay_frequency.required' => 'Pay frequency is required',
            'pay_frequency.integer' => 'Pay frequency must be an integer',
            'pay_frequency.in' => 'Pay frequency is invalid',

            'frequency_sequence.required' => 'Frequency sequence is required',
            'frequency_sequence.integer' => 'Frequency sequence must be an integer',
            'frequency_sequence.in' => 'Frequency sequence is invalid',

            'start_date.required' => 'Start date is required',
            'start_date.date_format' => 'Start date must match the format Y-m-d e.g.(2000-12-31)',

            'end_date.required' => 'End date is required',
            'end_date.date_format' => 'End date must match the format Y-m-d e.g.(2000-12-31)',

            'end_date.after_or_equal' => 'End date must be after or equal to start date',

            'remarks.max' => 'Remarks must not exceed 255 characters',
            'employee_ids.required' => 'Employees not found',
            'employee_ids.array' => 'Employees must be an array',
            'employee_ids.min' => 'Employees must have at least 1 item',
        ]);
    }
}
