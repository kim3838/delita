<?php

namespace App\Http\Requests\EmployeeContact;

use App\Concrete\ContactConcrete;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

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
                'email:rfc,dns',
                'different:personal_email',
                function ($attribute, $value, $fail) {

                    $contactService = new ContactConcrete();

                    $exceptEmployeeId = $this->route('employeeId');

                    if ($contactService->isEmailTaken($value, $exceptEmployeeId)) {

                        $fail('Office email has already been taken');
                    }
                },
            ],
            'personal_email' => [
                'nullable',
                'email:rfc,dns',
                'different:office_email',
                function ($attribute, $value, $fail) {

                    $contactService = new ContactConcrete();

                    $exceptEmployeeId = $this->route('employeeId');

                    if ($contactService->isEmailTaken($value, $exceptEmployeeId)) {

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

                    $contactService = new ContactConcrete();

                    $exceptEmployeeId = $this->route('employeeId');

                    $taken = $contactService->isPhoneTaken($value, $exceptEmployeeId);

                    if ($taken) {

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

                    $contactService = new ContactConcrete();

                    $exceptEmployeeId = $this->route('employeeId');

                    $taken = $contactService->isPhoneTaken($value, $exceptEmployeeId);

                    if ($taken) {

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
}
