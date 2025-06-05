<?php

namespace App\Http\Requests\IncomeTax;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\IncomeTax;

class UpdateIncomeTaxRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $incomeTax = IncomeTax::findOrfail($this->route('incomeTaxId'));

        return $this->user()->can('update', $incomeTax);
    }
}
