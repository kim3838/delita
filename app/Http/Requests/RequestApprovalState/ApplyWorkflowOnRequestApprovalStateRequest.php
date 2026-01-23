<?php

namespace App\Http\Requests\RequestApprovalState;

use App\Enums\RequestApprovalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyWorkflowOnRequestApprovalStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => 'required|numeric|exists:accounts,id',
            'company_id' => 'required|numeric|exists:companies,id',
            'remarks' => 'nullable|string|max:255',
            'action' => [
                'required',
                'numeric',
                Rule::in([
                    RequestApprovalStatus::APPROVED,
                    RequestApprovalStatus::DECLINED
                ])
            ],
            'approval_states' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => 'Account id is required',
            'account_id.exists' => 'Account does not exist',
            'company_id.required' => 'Company id is required',
            'company_id.exists' => 'Company does not exist',
            'remarks.max' => 'Remarks must not be greater than 255 characters',
            'action.required' => 'Action is required',
            'action.in' => 'Invalid action',
            'approval_states.required' => 'Approval states is required',
            'approval_states.array' => 'Approval states must be an array',
        ];
    }
}
