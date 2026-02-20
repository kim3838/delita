<?php

namespace App\Http\Requests\Payroll;

use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;

class ViewPayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payroll = Payroll::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        return $this->user()->can('view', $payroll);
    }
}
