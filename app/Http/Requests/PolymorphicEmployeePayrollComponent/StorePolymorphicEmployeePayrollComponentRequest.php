<?php

namespace App\Http\Requests\PolymorphicEmployeePayrollComponent;

use App\Http\Requests\BasePolymorphicEmployeePayrollComponentRequest;
use App\Models\EmployeePayrollComponent;

class StorePolymorphicEmployeePayrollComponentRequest extends BasePolymorphicEmployeePayrollComponentRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EmployeePayrollComponent::class);
    }
}
