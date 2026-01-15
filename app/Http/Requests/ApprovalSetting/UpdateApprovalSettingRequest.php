<?php

namespace App\Http\Requests\ApprovalSetting;

use App\Models\ApprovalSetting;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateApprovalSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $approvalSetting = ApprovalSetting::query()->findOrfail($this->route('approvalSettingId'));

        return $this->user()->can('update', $approvalSetting);
    }

    public function rules(): array
    {
        return [
            'account_id' => 'required|numeric|exists:accounts,id',
            'company_id' => 'required|numeric|exists:companies,id',
            'approval_setting_id' => 'required|numeric|exists:approval_settings,id',
            'approver_sequence' => 'array',

            'approver_sequence.*.id' => ['nullable', 'integer'],
            'approver_sequence.*.approval_setting_id' => [
                'required',
                'integer',
                'exists:approval_settings,id'
            ],
            'approver_sequence.*.order' => [
                'required',
                'integer',
                'min:1',
                function($attribute, $value, $fail){
                    //Order must be in order
                    $orders = collect($this->input('approver_sequence'))
                        ->pluck('order')
                        ->sort()
                        ->values();

                    $expected = collect(range(1, $orders->count()));

                    if (!($orders == $expected)) {
                        $fail('Order must be sequential starting from 1.');
                    }
                }
            ],
            'approver_sequence.*.approver_id' => [
                'distinct',
                function ($attribute, $value, $fail) {

                    $index = $this->getApproverSequenceIndex($attribute);
                    $approvalSequence = $this->input("approver_sequence.{$index}");

                    $user = User::query()->find($approvalSequence['approver_id']);

                    if(empty($user)){
                        $fail('Approval sequence: approver does not exist');
                    }
                }
            ],

            'spliced_approver_sequence' => ['array'],
            'spliced_approver_sequence.*' => ['integer'],
        ];
    }

    private function getApproverSequenceIndex($attribute): int
    {
        preg_match('/approver_sequence\.(\d+)\./', $attribute, $matches);
        return (int)$matches[1];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company id is required',
            'company_id.exists' => 'Company does not exist',
            'account_id.required' => 'Account id is required',
            'account_id.exists' => 'Account does not exist',
            'approval_setting_id.required' => 'Approval setting id is required',
            'approval_setting_id.exists' => 'Approval setting does not exist',

            'approver_sequence.array' => 'Approver sequence must be an array',
            'approver_sequence.*.approval_setting_id.exists' => 'Approval setting does not exist',
            'approver_sequence.*.approval_setting_id.required' => 'Approval setting is required',
            'approver_sequence.*.order.min' => 'Approval sequence: order must be greater than 0',
            'approver_sequence.*.approver_id.distinct' => 'Approval sequence: approver must not have a duplicate',

            'spliced_approver_sequence.array' => 'Spliced approvers must be an array',
            'spliced_approver_sequence.*.integer' => 'Spliced approvers must be an integer',
        ];
    }
}
