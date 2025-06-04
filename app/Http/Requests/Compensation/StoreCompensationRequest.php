<?php

namespace App\Http\Requests\Compensation;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\Compensation;

class StoreCompensationRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Compensation::class);
    }
}
