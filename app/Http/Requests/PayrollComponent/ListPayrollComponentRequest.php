<?php

namespace App\Http\Requests\PayrollComponent;

use App\Models\Hydrations\PayrollComponent;
use Illuminate\Foundation\Http\FormRequest;

class ListPayrollComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', PayrollComponent::class);
    }
}
