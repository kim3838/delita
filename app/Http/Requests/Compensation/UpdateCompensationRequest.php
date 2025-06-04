<?php

namespace App\Http\Requests\Compensation;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\Compensation;

class UpdateCompensationRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $compensation = Compensation::findOrfail($this->route('compensationId'));

        return $this->user()->can('update', $compensation);
    }
}
