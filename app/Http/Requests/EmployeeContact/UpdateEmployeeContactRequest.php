<?php

namespace App\Http\Requests\EmployeeContact;

use App\Models\Employee;
use App\Models\EmployeeContact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class UpdateEmployeeContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employee = Employee::query()->findOrfail($this->route('employeeId'));

        return $this->user()->can('update', $employee);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer',
            'employee_id' => 'required|numeric|integer',
            'office_email' => [
                'nullable',
                'email:rfc',
                'different:personal_email',
                function ($attribute, $value, $fail) {
                    if ($value && (App::environment('production') && $this->isEmailTaken($value))) {
                        $fail('Office email has already been taken');
                    }
                },
            ],
            'personal_email' => [
                'nullable',
                'email:rfc',
                'different:office_email',
                function ($attribute, $value, $fail) {
                    if ($value && (App::environment('production') && $this->isEmailTaken($value))) {
                        $fail('Personal email has already been taken');
                    }
                },

            ],
            'office_phone' => [
                'nullable',
                'string',
                'max:255',
                'different:personal_phone',
                function ($attribute, $value, $fail) {
                    if ($value && (App::environment('production') && $this->isPhoneTaken($value))) {
                        $fail('Office phone has already been taken');
                    }
                },
            ],
            'personal_phone' => [
                'nullable',
                'string',
                'max:255',
                'different:office_phone',
                function ($attribute, $value, $fail) {
                    if ($value && (App::environment('production') && $this->isPhoneTaken($value))) {
                        $fail('Personal phone has already been taken');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'employee_id.required' => 'Employee account is required',
            'office_email.email' => 'The office email must be a valid email address',
            'office_email.different' => 'The office email and personal email must be different',
            'personal_email.email' => 'The personal email must be a valid email address',
            'personal_email.different' => 'The personal email and office email must be different',
            'office_phone.different' => 'The office phone and personal phone must be different',
            'personal_phone.different' => 'The personal phone and office phone must be different',
            'office_phone.unique' => 'The office phone has already been taken',
            'personal_phone.unique' => 'The personal phone has already been taken',
        ];

    }

    private function isEmailTaken(string $email): bool
    {
        $queryBuilder = EmployeeContact::getQuery()
            ->where('employee_id', '!=', $this->route('employeeId'))
            ->where(function ($query) use ($email) {
                $query->where('office_email', $email)
                    ->orWhere('personal_email', $email);
            });

        return $queryBuilder->exists();
    }
    private function isPhoneTaken(string $phone): bool
    {
        $queryBuilder = EmployeeContact::getQuery()
            ->where('employee_id', '!=', $this->route('employeeId'))
            ->where(function ($query) use ($phone) {
                $query->where('office_phone', $phone)
                    ->orWhere('personal_phone', $phone);
            });

        return $queryBuilder->exists();
    }
}
