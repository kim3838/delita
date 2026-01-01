<?php

namespace App\Http\Requests\LeaveRunningBalance;

use App\Models\Hydrations\LeaveRunningBalanceReport;
use Illuminate\Foundation\Http\FormRequest;

class LeaveRunningBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', LeaveRunningBalanceReport::class);
    }
}
