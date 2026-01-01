<?php

namespace App\Http\Requests\EmployeeLeaveType;

use App\Models\EmployeeLeaveType;
use Illuminate\Foundation\Http\FormRequest;

class ListEmployeeLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', EmployeeLeaveType::class);
    }
}
