<?php

namespace App\Http\Requests\PayrollRequest;

use App\Models\PayrollRequest;
use Illuminate\Foundation\Http\FormRequest;

class ViewPayrollRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payrollRequest = PayrollRequest::query()->where('number', $this->route('requestNumber'))->firstOrFail();

        return $payrollRequest instanceof PayrollRequest;
    }
}
