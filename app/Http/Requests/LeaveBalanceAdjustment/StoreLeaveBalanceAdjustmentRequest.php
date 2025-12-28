<?php

namespace App\Http\Requests\LeaveBalanceAdjustment;

use App\Models\LeaveBalanceAdjustment;

class StoreLeaveBalanceAdjustmentRequest extends BaseLeaveBalanceAdjustmentStoreAndUpdateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LeaveBalanceAdjustment::class);
    }
}
