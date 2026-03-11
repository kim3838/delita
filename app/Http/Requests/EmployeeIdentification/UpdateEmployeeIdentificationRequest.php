<?php

namespace App\Http\Requests\EmployeeIdentification;

use App\Models\EmployeeIdentification;

class UpdateEmployeeIdentificationRequest extends BaseStoreAndUpdateEmployeeIdentificationRequest
{
    public function authorize(): bool
    {
        $employeeIdentification = EmployeeIdentification::query()->findOrFail($this->route('employeeIdentificationId'));

        return $this->user()->can('update', $employeeIdentification);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'employee_id' => 'required|numeric|integer',
            'type' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {

                    $employeeId = $this->input('employee_id');

                    $existingType = EmployeeIdentification::query()
                        ->where('employee_id', $employeeId)
                        ->whereNot('id', $this->route('employeeIdentificationId'))
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
