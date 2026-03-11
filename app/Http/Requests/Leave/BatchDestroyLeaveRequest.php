<?php

namespace App\Http\Requests\Leave;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\LeaveRepository;
use App\Models\Leave;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Leave::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'leave_ids' => 'required|array',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $bulkDeleteAborted = false;
            $leaveIds = $this->input('leave_ids', []);
            $leaves = app(LeaveRepository::class)->list((object)[
                'leave_ids' => $leaveIds
            ]);

            foreach ($leaves as $leave) {

                $payrollService = app(PayrollServiceInterface::class, [$leave->employee->company]);

                $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($leave->employee, $leave->date);

                if(!$bulkDeleteAborted && $isDateOnAnyPayrollStatementAttendance){

                    $bulkDeleteAborted = true;

                    $validator->errors()->add(
                        'bulk_delete_aborted',
                        'Bulk delete aborted'
                    );
                }

                if($isDateOnAnyPayrollStatementAttendance){
                    $validator->errors()->add(
                        $leave->employee->number . $leave->date->toDateString(),
                        'Unable to delete ' . $leave->employee->number . "'s " . $leave->date->toDateString() . ' leave, payroll generated.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'leave_ids.required' => 'Leave ids is required',
            'leave_ids.array' => 'Leave ids must be an array',
        ];
    }
}
