<?php

namespace App\Http\Requests\Payroll;

use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\PayrollStatus;
use App\Models\Payroll;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Validator;

class StorePayrollRequest extends BasePayrollGenerationRequest
{
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
