<?php

namespace App\Http\Requests\EmployeeShift;

use App\Models\EmployeeShift;
use Illuminate\Foundation\Http\FormRequest;

class ListEmployeeShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', EmployeeShift::class);
    }
}
