<?php

namespace App\Http\Requests\LeaveType;

use App\Enums\RegexValidation;
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
                'regex:' . RegexValidation::NO_WHITESPACE->value,
                'max:255',
                Rule::unique('leave_types')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],
        ], parent::rules());
    }

}
