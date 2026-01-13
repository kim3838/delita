<?php

namespace App\Http\Requests\EmployeePortal\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class ViewEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employee = Employee::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        return $employee instanceof Employee;
    }
}
