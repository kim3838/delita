<?php

namespace App\Http\Requests\LeaveType;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;

class ListLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', LeaveType::class);
    }
}
