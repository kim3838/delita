<?php

namespace App\Http\Requests\PayrollRequest;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\PayrollRepository;
use App\Enums\PayrollStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollRequest;
use App\Traits\HasApproval;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
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

            if ($payrollRequestAlreadyExists) {

                $validator->errors()->add(
                    'payroll_request',
                    'Payroll request already exists.'
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
