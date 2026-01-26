<?php

namespace App\Http\Requests\LeaveRequest;

use App\Models\Leave;
use App\Models\LeaveRequest;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;

class BaseStoreLeaveRequestRequest extends FormRequest
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
            'employee_id' => 'required|numeric|exists:employees,id',
            'leave_type_id' => [
                'required',
                'numeric',
                'integer',
                function ($attribute, $value, $fail) {

                    $leaveType = Leave::query()->find($value);

                    if(!$leaveType){
                        $fail('Leave type not found');
                    }

                    $companyId = $this->input('company_id');
                    $employeeId = $this->input('employee_id');

                    /**
                     * Validate if there are approvers
                     **/
                    $approversArray = $this->getRequestableApprovers(
                        Relation::getMorphAlias(LeaveRequest::class),
                        $employeeId,
                        $companyId,
                        $this->user()->id
                    );

                    if(empty($approversArray)){
                        $fail('No approvers found for this request.');
                    }
                }
            ],
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'remarks' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'company_id.required' => 'Company is required',
            'employee_id.exists' => 'Employee not found',
            'employee_id.required' => 'Employee is required',
            'employee_id.numeric' => 'Employee id must be numeric',
            'leave_type_id.exists' => 'Leave type not found',
            'leave_type_id.required' => 'Leave type is required',
            'leave_type_id.numeric' => 'Leave type id must be numeric',
            'date_from.required' => 'Date from is required',
            'date_from.date_format' => 'Date from must be in Y-m-d format',
            'date_to.required' => 'Date to is required',
            'date_to.date_format' => 'Date to must be in Y-m-d format',
            'date_to.after_or_equal' => 'Date to must be after or equal to date from',
            'remarks.max' => 'Remarks must not exceed 255 characters'
        ]);
    }
}
