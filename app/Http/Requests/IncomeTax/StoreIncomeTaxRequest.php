<?php

namespace App\Http\Requests\IncomeTax;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\IncomeTax;

class StoreIncomeTaxRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', IncomeTax::class);
    }
}
