<?php

namespace App\Http\Requests\LeaveRequest;

use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;

class ListLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', LeaveRequest::class);
    }
}
