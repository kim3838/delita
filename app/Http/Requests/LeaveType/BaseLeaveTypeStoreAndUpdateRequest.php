<?php

namespace App\Http\Requests\LeaveType;

use App\Enums\LeaveCarryOverType;
use App\Enums\LeaveIntervalSpanType;
use App\Enums\LeavePeriodType;
use App\Enums\LeaveType;
use App\Enums\LeaveUsageSpanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BaseLeaveTypeStoreAndUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => [
                'required',
                'integer',
                Rule::in([
                    LeaveType::VACATION->value,
                    LeaveType::SICK->value,
                    LeaveType::EMERGENCY->value,
                ])
            ],
            'is_paid' => ['required', 'boolean'],
            'monetizable' => ['required', 'boolean'],

            'eligibility_employment_types' => ['required', 'array'],
            'initial_balance_upon_eligibility' => ['required', 'integer','min:0', 'max:999999'],

            'period_type' => [
                'required',
                'integer',
                Rule::in([
                    LeavePeriodType::CALENDAR_YEAR->value,
                    LeavePeriodType::INTERVAL->value,
                ])
            ],
            'period_interval_span_type' => [
                'nullable',
                'integer',
                Rule::in([
                    LeaveIntervalSpanType::YEAR->value,
                    LeaveIntervalSpanType::MONTH->value,
                    LeaveIntervalSpanType::DAY->value,
                ])
            ],
            'period_interval_span_value' => [
                'required',
                'integer',
                function($attribute, $value, $fail){

                    if(in_array($this->input('period_type'), [LeavePeriodType::CALENDAR_YEAR->value, LeavePeriodType::INTERVAL->value])){

                        if($this->input('period_type') == LeavePeriodType::INTERVAL->value){

                            if($value == 0){
                                $fail('Interval span value must be greater than 0.');
                            }

                            if((int)$value < 0 || (int)$value > 999999){
                                $fail('Invalid interval span value.');
                            }
                        }

                    } else {
                        $fail('Invalid period type.');
                    }
                }
            ],
            'period_calendar_span_value' => ['required', 'integer'],

            'limit_usage' => ['required', 'boolean'],
            'limit_usage_span_type' => [
                'required',
                'integer',
                Rule::in([
                    LeaveUsageSpanType::YEAR->value,
                    LeaveUsageSpanType::MONTH->value,
                    LeaveUsageSpanType::DAY->value,
                ])
            ],
            'limit_usage_span_value' => [
                'required',
                'integer',
                function($attribute, $value, $fail){
                    if($this->input('limit_usage')){
                        if($value == 0){
                            $fail('Limit usage span value must be greater than 0.');
                        }

                        if((int)$value < 0 || (int)$value > 999999){
                            $fail('Invalid limit usage span value.');
                        }
                    }
                }
            ],
            'limit_usage_value' => [
                'required',
                'integer',
                function($attribute, $value, $fail){
                    if($this->input('limit_usage')){
                        if($value == 0){
                            $fail('Limit usage value must be greater than 0.');
                        }

                        if((int)$value < 0 || (int)$value > 999999){
                            $fail('Invalid limit usage value.');
                        }
                    }
                }
            ],

            'carry_over_balance_per_new_period' => ['required', 'boolean'],
            'carry_over_balance_type' => [
                'required',
                'integer',
                Rule::in([
                    LeaveCarryOverType::ALL->value,
                    LeaveCarryOverType::LIMIT->value,
                ])
            ],
            'carry_over_balance_value' => [
                'required',
                'integer',
                function($attribute, $value, $fail){
                    if($this->input('carry_over_balance_per_new_period')){

                        if((int)$value < 0 || (int)$value > 999999){
                            $fail('Invalid carry over limit.');
                        }
                    }
                }
            ],

            'leave_type_balance_per_period' => ['array'],
            'spliced_leave_type_balance_per_period' => ['array'],
            'leave_type_balance_per_period.*.id' => ['nullable', 'integer'],
            'leave_type_balance_per_period.*.leave_type_id' => ['nullable', 'integer', 'exists:leave_types,id'],
            'leave_type_balance_per_period.*.from_period' => ['required', 'integer', 'min:1', 'max:999999'],
            'leave_type_balance_per_period.*.and_so_on' => ['required', 'boolean'],
            'leave_type_balance_per_period.*.to_period' => [
                function($attribute, $value, $fail){

                    $index = $this->getBalancePerPeriodIndex($attribute);
                    $balancePerPeriod = $this->input("leave_type_balance_per_period.{$index}");

                    if(!$balancePerPeriod['and_so_on']){

                        if(!is_numeric($balancePerPeriod['to_period']) || (int)$balancePerPeriod['to_period'] > 999999){
                            $fail('Balance per period: to period is invalid');
                        } else if(is_numeric($balancePerPeriod['from_period']) && $balancePerPeriod['to_period'] < $balancePerPeriod['from_period']){
                            $fail('Balance per period: to period must not be lesser than from period.');
                        }
                    }
                }
            ],
            'leave_type_balance_per_period.*.balance' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }

    private function getBalancePerPeriodIndex($attribute): int
    {
        preg_match('/leave_type_balance_per_period\.(\d+)\./', $attribute, $matches);
        return (int)$matches[1];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Code is required.',
            'code.regex' => 'Code must not contain spaces.',
            'code.unique' => 'Code has already been taken.',
            'name.required' => 'Name is required.',
            'type.required' => 'Type is required.',
            'type.in' => 'Type is invalid.',
            'is_paid.required' => 'Is paid is required.',
            'is_paid.boolean' => 'Is paid must be a boolean.',
            'monetizable.required' => 'Monetizable is required.',
            'monetizable.boolean' => 'Monetizable must be a boolean.',

            'limit_usage.required' => 'Limit usage is required.',
            'limit_usage.boolean' => 'Limit usage must be a boolean.',
            'limit_usage_span_type.required' => 'Limit usage span type is required.',
            'limit_usage_span_type.in' => 'Limit usage span type is invalid.',
            'limit_usage_span_value.required' => 'Limit usage span value is required.',
            'limit_usage_value.required' => 'Limit usage value is required.',

            'eligibility_employment_types.required' => 'Eligibility employment types is required.',
            'initial_balance_upon_eligibility.required' => 'Eligibility balance is required.',
            'initial_balance_upon_eligibility.integer' => 'Eligibility balance must be an integer.',
            'initial_balance_upon_eligibility.min' => 'Eligibility balance is invalid.',
            'initial_balance_upon_eligibility.max' => 'Eligibility balance is invalid.',

            'period_type.required' => 'Period type is required.',
            'period_type.in' => 'Period type is invalid.',
            'period_interval_span_type.required' => 'Period interval span type is required.',
            'period_interval_span_type.in' => 'Period interval span type is invalid.',
            'period_interval_span_value.required' => 'Period interval span value is required.',
            'period_calendar_span_value.required' => 'Period calendar span value is required.',

            'carry_over_balance_per_new_period.required' => 'Carry over balance per new period is required.',
            'carry_over_balance_per_new_period.boolean' => 'Carry over balance per new period must be a boolean.',
            'carry_over_balance_type.required' => 'Carry over balance type is required.',
            'carry_over_balance_type.in' => 'Carry over balance type is invalid.',
            'carry_over_balance_value.required' => 'Carry over balance value is required.',

            'leave_type_balance_per_period.array' => 'Balance per period: must be an array.',
            'spliced_leave_type_balance_per_period.array' => 'Spliced balance per period: must be an array.',

            'leave_type_balance_per_period.*.leave_type_id.required' => 'Balance per period: Leave type is required.',
            'leave_type_balance_per_period.*.leave_type_id.exists' => 'Balance per period: Leave type does not exist.',

            'leave_type_balance_per_period.*.from_period.required' => 'Balance per period: from-period is invalid / not found.',
            'leave_type_balance_per_period.*.from_period.min' => 'Balance per period: from-period is invalid.',
            'leave_type_balance_per_period.*.from_period.max' => 'Balance per period: from-period is invalid.',
            'leave_type_balance_per_period.*.from_period.integer' => 'Balance per period: from-period must be an integer.',

            'leave_type_balance_per_period.*.and_so_on.required' => 'Balance per period: and-so-on is required.',
            'leave_type_balance_per_period.*.and_so_on.boolean' => 'Balance per period: and-so-on must be a boolean.',

            'leave_type_balance_per_period.*.balance.required' => 'Balance per period: balance is invalid / not found.',
            'leave_type_balance_per_period.*.balance.integer' => 'Balance per period: balance must be an integer.',
            'leave_type_balance_per_period.*.balance.min' => 'Balance per period: balance is invalid.',
            'leave_type_balance_per_period.*.balance.max' => 'Balance per period: balance is invalid.',
        ];
    }
}
