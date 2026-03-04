<?php

namespace App\Http\Requests\PreGeneratePayroll;

use App\Http\Requests\Payroll\BasePayrollGenerationRequest;
use Illuminate\Support\Arr;

class PreGeneratePayrollRequest extends BasePayrollGenerationRequest
{
    public function rules(): array
    {
        return Arr::except($this->baseRules(), ['employee_ids']);
    }
}
