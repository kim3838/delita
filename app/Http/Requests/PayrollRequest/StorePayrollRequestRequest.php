<?php

namespace App\Http\Requests\PayrollRequest;

use App\Models\PayrollRequest;

class StorePayrollRequestRequest extends BaseStorePayrollRequestRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PayrollRequest::class);
    }
}
