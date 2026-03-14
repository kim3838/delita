<?php

namespace App\Http\Requests\SalaryStatementDetailManualAddDetail;

use App\Enums\FormulableComponentSubType;
use App\Enums\PayrollStatus;
use App\Enums\RegexValidation;
use App\Models\SalaryStatement;
use Illuminate\Foundation\Http\FormRequest;

class SalaryStatementManualAddDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        $salaryStatement = SalaryStatement::query()->where('ulid', $this->route('salaryStatementUlid'))->firstOrFail();

        return $this->user()->can('update', $salaryStatement);
    }

    public function rules(): array
    {
        return [
            'account_id' => 'required|numeric|exists:accounts,id',
            'company_id' => 'required|numeric|exists:companies,id',
            'refetch_payroll_ulid' => 'sometimes|required|string|exists:payrolls,ulid',
            'manual_add_details' => [
                'array'
            ],

            'manual_add_details.*.component_sub_type' => [
                'required',
                'string',
                function($attribute, $value, $fail){

                    $manualableComponentSubTypes = [
                        FormulableComponentSubType::MANUAL_EARNING->value,
                        FormulableComponentSubType::MANUAL_DEDUCTION->value,
                    ];

                    if(!in_array($value, $manualableComponentSubTypes)){
                        $fail('Invalid payroll item');
                    }
                }
            ],
            'manual_add_details.*.component_name' => [
                'required',
                'string',
                'max:100'
            ],
            'manual_add_details.*.amount' => [
                'required',
                'numeric',
                'min:0.01',
                'regex:' . RegexValidation::NUMERIC_12_DIGITS_2_DECIMALS->value,
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $salaryStatement = SalaryStatement::query()->where('ulid', $this->route('salaryStatementUlid'))->firstOrFail();
            $payroll = $salaryStatement->payroll;

            $payrollStatusIsDraft = $payroll->status == PayrollStatus::DRAFT;

            if (!$payrollStatusIsDraft) {

                $validator->errors()->add(
                    'salary_statement_manual_add_detail',
                    'Unable to proceed, payroll status is ' . $payroll->status->label()
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company id is required',
            'company_id.exists' => 'Company does not exist',
            'refetch_payroll_ulid.required' => 'Refetch payroll ulid is required',
            'refetch_payroll_ulid.exists' => 'Refetch payroll does not exist',
            'refetch_payroll_ulid.string' => 'Refetch payroll ulid must be a string',
            'account_id.required' => 'Account id is required',
            'account_id.exists' => 'Account does not exist',
            'approval_setting_id.required' => 'Approval setting id is required',
            'approval_setting_id.exists' => 'Approval setting does not exist',

            'manual_add_details.array' => 'Payroll items must be an array',
            'manual_add_details.*.component_sub_type.required' => 'Payroll item type is required',
            'manual_add_details.*.component_sub_type.string' => 'Payroll item type must be a string',
            'manual_add_details.*.component_sub_type.in' => 'Payroll item type is invalid',

            'manual_add_details.*.component_name.required' => 'Payroll item name is required',
            'manual_add_details.*.component_name.max' => 'Payroll item name must be less than 100 characters',
            'manual_add_details.*.component_name.string' => 'Payroll item name must be a string',

            'manual_add_details.*.amount.required' => 'Payroll item amount is required',
            'manual_add_details.*.amount.numeric' => 'Payroll item amount must be a number',
            'manual_add_details.*.amount.min' => 'Payroll item amount must be greater than 0.00',
            'manual_add_details.*.amount.regex' => 'Payroll item amount must be a valid number with up to 12 digits and maximum 2 decimal places',
        ];
    }
}
