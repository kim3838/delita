<?php

namespace App\Http\Requests\PayrollRequest;

use App\Enums\PayrollStatus;
use App\Models\Payroll;
use App\Models\PayrollRequest;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BaseStorePayrollRequestRequest extends FormRequest
{
    use HasApproval;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer|exists:companies,id',
            'payroll_id' => 'required|numeric|integer|exists:payrolls,id',
            'remarks' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $payrollRequestAlreadyExists = PayrollRequest::query()->where('payroll_id', $this->input('payroll_id'))->first();

            $payrollIsCompleted = Payroll::query()->where('id', $this->input('payroll_id'))->where('status', PayrollStatus::COMPLETED->value)->first();

            if ($payrollRequestAlreadyExists) {

                $validator->errors()->add(
                    'payroll_request',
                    'Payroll request already exists.'
                );
            }

            if ($payrollIsCompleted) {
                $validator->errors()->add(
                    'payroll_request',
                    'Payroll is already completed.'
                );
            }
        });
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $companyId = $this->get('company_id');

                $approversArray = $this->getRequestableApprovers(
                    Relation::getMorphAlias(PayrollRequest::class),
                    null,
                    $companyId
                );

                if(empty($approversArray)){
                    $validator->errors()->add(
                        'payroll_request', 'No approvers found for payroll request.'
                    );
                }
            }
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'company_id.required' => 'Company is required',
            'payroll_id.required' => 'Payroll is required',
            'payroll_id.exists' => 'Payroll not found',
            'remarks.max' => 'Remarks must not exceed 255 characters'
        ]);
    }
}
