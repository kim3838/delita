<?php

namespace App\Imports;

use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImportArray implements toArray, WithHeadingRow, WithValidation
{
    public function __construct(
        protected $companyId
    ){}

    public function array(array $array)
    {
        // TODO: Implement array() method.
    }

    public function rules(): array
    {

        return [
            'number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees')->where(function ($query) {
                    return $query->where('company_id', $this->companyId);
                }),
            ],
            'family_name' => 'required|string|max:255',
            'given_name' => 'required|string|max:255',

        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'number.required' => 'Employee number is required.',
            'number.unique' => 'Employee number already in use.',
            'family_name.required' => 'Family name is required.',
            'given_name.required' => 'Given name is required.',
        ];
    }
}
