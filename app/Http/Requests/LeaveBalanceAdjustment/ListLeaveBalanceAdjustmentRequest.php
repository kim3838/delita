<?php

namespace App\Http\Requests\LeaveBalanceAdjustment;

use App\Models\LeaveBalanceAdjustment;
use Illuminate\Foundation\Http\FormRequest;

class ListLeaveBalanceAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', LeaveBalanceAdjustment::class);
    }
}
