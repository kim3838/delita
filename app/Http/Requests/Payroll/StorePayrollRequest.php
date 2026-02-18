<?php

namespace App\Http\Requests\Payroll;

use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\PayFrequency;
use App\Enums\PayrollStatus;
use App\Enums\SemiMonthlySequence;
use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Payroll::class);
    }

    public function rules(): array
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
                    PayFrequency::SEMI_MONTHLY,
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
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $companyId = $this->get('company_id');
                $year = $this->get('year');
                $month = $this->get('month');
                $payFrequency = $this->get('pay_frequency');
                $frequencySequence = $this->get('frequency_sequence');
                $startDate = $this->get('start_date');
                $endDate = $this->get('end_date');

                $payroll = App::make(PayrollRepository::class)->model()::where('company_id', $companyId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('pay_frequency', $payFrequency)
                    ->where('frequency_sequence', $frequencySequence)
                    ->where('start_date', $startDate)
                    ->where('end_date', $endDate)
                    ->whereNot('status', PayrollStatus::DRAFT->value)
                    ->first();

                if(!empty($payroll)){
                    $validator->errors()->add(
                        'payroll', 'Unable to regenerate payroll.'
                    );
                }
            }
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

            'remarks.max' => 'Remarks must not exceed 255 characters'
        ]);
    }

    public function passedValidation(): void
    {
        $companyId = $this->get('company_id');
        $year = $this->get('year');
        $month = $this->get('month');
        $payFrequency = $this->get('pay_frequency');
        $frequencySequence = $this->get('frequency_sequence');
        $startDate = $this->get('start_date');
        $endDate = $this->get('end_date');

        Payroll::query()
            ->where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('pay_frequency', $payFrequency)
            ->where('frequency_sequence', $frequencySequence)
            ->where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->first()
            ?->delete();
    }
}
