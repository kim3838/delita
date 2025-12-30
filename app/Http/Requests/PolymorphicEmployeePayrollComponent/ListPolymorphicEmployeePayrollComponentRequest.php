<?php

namespace App\Http\Requests\PolymorphicEmployeePayrollComponent;

use App\Models\EmployeePayrollComponent;
use Illuminate\Foundation\Http\FormRequest;

class ListPolymorphicEmployeePayrollComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', EmployeePayrollComponent::class);
    }
}
