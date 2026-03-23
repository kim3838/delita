<?php

namespace App\Http\Requests\EmployeeContact;

use App\Concrete\ContactConcrete;
use App\Models\EmployeeContact;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EmployeeContact::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer',
            'employee_id' => 'sometimes|required|numeric|integer',
            'office_email' => [
                'nullable',
                'email:rfc,dns',
                'different:personal_email',
                function ($attribute, $value, $fail) {

                    $employeeId = $this->input('employee_id');

                    $contactService = new ContactConcrete();

                    $taken = $contactService->isEmailTaken($value, $employeeId);

                    if ($taken) {

                        $fail('Office email has already been taken');
                    }
                },
            ],
            'personal_email' => [
                'nullable',
                'email:rfc,dns',
                'different:office_email',
                function ($attribute, $value, $fail) {

                    $employeeId = $this->input('employee_id');

                    $contactService = new ContactConcrete();

                    if ($contactService->isEmailTaken($value, $employeeId)) {

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

                    $taken = $contactService->isPhoneTaken($value);

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

                    $taken = $contactService->isPhoneTaken($value);

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
        ];

    }
}
