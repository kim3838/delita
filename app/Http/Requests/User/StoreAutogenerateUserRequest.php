<?php

namespace App\Http\Requests\User;

use App\Concrete\ContactConcrete;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreAutogenerateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer',
            'family_name' => 'required|string|max:255',
            'given_name' => 'required|string|max:255',
            'office_email' => [
                'required',
                function ($attribute, $value, $fail) {

                    $contactService = new ContactConcrete();

                    if ($contactService->isEmailTaken($value)) {

                        $fail('Email has already been taken');
                    }
                },
            ],
            'employable' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'family_name.required' => 'Family name is required',
            'given_name.required' => 'Given name is required',
            'office_email.required' => 'Office email is required',
            'office_email.unique' => 'Office email has already been taken as user email',
        ];
    }
}
