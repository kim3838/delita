<?php

namespace App\Http\Requests\LeaveRequest;

use App\Blueprint\PayrollServiceInterface;
use App\Http\Requests\LeaveDateRangeInquire\BaseLeaveDateRangeRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Traits\HasApproval;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Relations\Relation;

class BaseStoreLeaveRequestRequest extends BaseLeaveDateRangeRequest
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
            'shift_id' => 'required|numeric|integer|exists:shifts,id',
            'leave_type_id' => [
                'required',
                'numeric',
                'integer',
                function ($attribute, $value, $fail) {

                    $leaveType = LeaveType::query()->find($value);

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
            ...(parent::rules()),
            'remarks' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $leaveRequestAborted = false;
            $company = Company::query()->find($this->input('company_id'));
            $employee = Employee::query()->find($this->input('employee_id'));
            $dateFrom = $this->input('date_from', null);
            $dateTo = $this->input('date_to', null);
            $payrollService = app(PayrollServiceInterface::class, [$company]);

            if(!empty($dateFrom) && !empty($dateTo)){

                $dateFrom = Carbon::parse($dateFrom);
                $dateTo = Carbon::parse($dateTo);

                $datePeriod = CarbonPeriod::create($dateFrom, $dateTo);

                foreach($datePeriod as $date){

                    $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($employee, $date);

                    if(!$leaveRequestAborted && $isDateOnAnyPayrollStatementAttendance){

                        $leaveRequestAborted = true;

                        $validator->errors()->add(
                            'leave_request_aborted',
                            'Leave request aborted'
                        );
                    }

                    if($isDateOnAnyPayrollStatementAttendance){
                        $validator->errors()->add(
                            $date->toDateString(),
                            'Unable to leave request ' .$date->toDateString() . ', payroll generated.'
                        );
                    }
                }
            }
        });
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
            ...(parent::messages()),
            'remarks.max' => 'Remarks must not exceed 255 characters'
        ]);
    }
}
