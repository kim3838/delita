<?php

namespace App\Http\Requests\EmployeeIdentification;

use App\Models\EmployeeIdentification;
use Illuminate\Foundation\Http\FormRequest;

class DestroyEmployeeIdentificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employeeIdentification = EmployeeIdentification::query()->findOrFail($this->route('employeeIdentificationId'));

        return $this->user()->can('delete', $employeeIdentification);
    }
}
