<?php

namespace App\Http\Requests\LeaveRequest;

use App\Models\LeaveRequest;

class StoreLeaveRequestRequest extends BaseStoreLeaveRequestRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LeaveRequest::class);
    }
}
