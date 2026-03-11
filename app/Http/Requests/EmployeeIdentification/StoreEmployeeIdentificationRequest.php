<?php

namespace App\Http\Requests\EmployeeIdentification;

use App\Models\EmployeeIdentification;

class StoreEmployeeIdentificationRequest extends BaseStoreAndUpdateEmployeeIdentificationRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EmployeeIdentification::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'employee_id' => 'sometimes|required|numeric|integer',
            'type' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {

                    $employeeId = $this->input('employee_id');

                    $existingType = EmployeeIdentification::query()
                        ->where('employee_id', $employeeId)
                        ->where('type', $value)
                        ->exists();

                    if ($existingType) {
                        $fail('Identification already exists');
                    }
                }
            ],
        ]);
    }
}
