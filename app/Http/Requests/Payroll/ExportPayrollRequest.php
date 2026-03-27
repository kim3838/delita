<?php

namespace App\Http\Requests\Payroll;

use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;

class ExportPayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('export', Payroll::class);
    }
}
