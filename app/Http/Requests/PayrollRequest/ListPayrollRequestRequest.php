<?php

namespace App\Http\Requests\PayrollRequest;

use App\Models\PayrollRequest;
use Illuminate\Foundation\Http\FormRequest;

class ListPayrollRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', PayrollRequest::class);
    }
}
