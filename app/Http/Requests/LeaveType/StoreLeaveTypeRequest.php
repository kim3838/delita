<?php

namespace App\Http\Requests\LeaveType;

use App\Models\LeaveType;
use Illuminate\Validation\Rule;

class StoreLeaveTypeRequest extends BaseLeaveTypeStoreAndUpdateRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LeaveType::class);
    }

    public function rules(): array
    {
        return array_merge([
            'code' => [
                'required',
                'string',
                'regex:/^\S+$/',//Must not contain any spaces, tabs, or line breaks
                'max:255',
                Rule::unique('leave_types')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],
        ], parent::rules());
    }

}
