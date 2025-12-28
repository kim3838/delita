<?php

namespace App\Http\Requests\LeaveBalanceAdjustment;

use App\Models\LeaveBalanceAdjustment;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyLeaveBalanceAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', LeaveBalanceAdjustment::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'leave_balance_adjustment_ids' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'leave_balance_adjustment_ids.required' => 'Leave balance adjustment ids is required',
            'leave_balance_adjustment_ids.array' => 'Leave balance adjustment ids must be an array',
        ];
    }
}
