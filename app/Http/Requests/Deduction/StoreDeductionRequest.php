<?php

namespace App\Http\Requests\Deduction;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\Deduction;

class StoreDeductionRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Deduction::class);
    }
}
