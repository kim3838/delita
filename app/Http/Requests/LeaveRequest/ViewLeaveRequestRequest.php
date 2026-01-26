<?php

namespace App\Http\Requests\LeaveRequest;

use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;

class ViewLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $leaveRequest = LeaveRequest::query()->where('number', $this->route('requestNumber'))->firstOrFail();

        return $leaveRequest instanceof LeaveRequest;
    }
}
