<?php

namespace App\Http\Requests\Overtime;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\OvertimeRepository;
use App\Models\Overtime;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Overtime::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'overtime_ids' => 'required|array',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $bulkDeleteAborted = false;
            $overtimeIds = $this->input('overtime_ids', []);
            $overtimes = app(OvertimeRepository::class)->list((object)[
                'overtime_ids' => $overtimeIds
            ]);

            foreach ($overtimes as $overtime) {

                $payrollService = app(PayrollServiceInterface::class, [$overtime->attendance->employee->company]);

                $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($overtime->attendance->employee, $overtime->attendance->date);

                if(!$bulkDeleteAborted && $isDateOnAnyPayrollStatementAttendance){

                    $bulkDeleteAborted = true;

                    $validator->errors()->add(
                        'bulk_delete_aborted',
                        'Bulk delete aborted'
                    );
                }

                if($isDateOnAnyPayrollStatementAttendance){
                    $validator->errors()->add(
                        $overtime->attendance->employee->number . $overtime->attendance->date->toDateString(),
                        'Unable to delete ' . $overtime->attendance->employee->number . "'s " . $overtime->attendance->date->toDateString() . ' overtime, payroll generated.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'overtime_ids.required' => 'Overtime ids is required',
            'overtime_ids.array' => 'Overtime ids must be an array',
        ];
    }
}
