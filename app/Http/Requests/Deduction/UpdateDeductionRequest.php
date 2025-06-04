<?php

namespace App\Http\Requests\Deduction;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\Deduction;

class UpdateDeductionRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $deduction = Deduction::findOrfail($this->route('deductionId'));

        return $this->user()->can('update', $deduction);
    }
}
