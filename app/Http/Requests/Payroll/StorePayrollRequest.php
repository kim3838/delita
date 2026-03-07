<?php

namespace App\Http\Requests\Payroll;

use App\Models\Payroll;

class StorePayrollRequest extends BasePayrollGenerationRequest
{
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
