<?php

namespace App\Http\Requests\LeaveType;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;

class ViewLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $leaveType = LeaveType::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        return $this->user()->can('view', $leaveType);
    }
}
