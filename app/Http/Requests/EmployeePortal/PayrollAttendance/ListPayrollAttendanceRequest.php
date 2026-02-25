<?php

namespace App\Http\Requests\EmployeePortal\PayrollAttendance;

use Illuminate\Foundation\Http\FormRequest;

class ListPayrollAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|exists:companies,id',
            'payroll_id' => 'required|numeric|exists:payrolls,id'
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company not found.',
            'company_id.exists' => 'Company not found.',
            'payroll_id.required' => 'Payroll not found, select a payroll.',
            'payroll_id.exists' => 'Payroll not found.',
        ];
    }
}
