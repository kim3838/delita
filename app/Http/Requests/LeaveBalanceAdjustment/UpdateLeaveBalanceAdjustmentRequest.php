<?php

namespace App\Http\Requests\LeaveBalanceAdjustment;

use App\Models\LeaveBalanceAdjustment;

class UpdateLeaveBalanceAdjustmentRequest extends BaseLeaveBalanceAdjustmentStoreAndUpdateRequest
{
    public function authorize(): bool
    {
        $leaveBalanceAdjustment = LeaveBalanceAdjustment::query()->where('ulid', $this->route('leaveBalanceAdjustmentUlid'))->firstOrFail();

        return $this->user()->can('update', $leaveBalanceAdjustment);
    }
}
