<?php

namespace App\Http\Requests\Attendance;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Attendance::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'attendance_ids' => 'required|array',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $bulkDeleteAborted = false;
            $attendanceIds = $this->input('attendance_ids', []);
            $attendances = app(AttendanceRepository::class)->list((object)[
                'attendance_ids' => $attendanceIds
            ]);

            foreach ($attendances as $attendance) {

                $payrollService = app(PayrollServiceInterface::class, [$attendance->employee->company]);

                $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($attendance->employee, $attendance->date);

                if(!$bulkDeleteAborted && $isDateOnAnyPayrollStatementAttendance){

                    $bulkDeleteAborted = true;

                    $validator->errors()->add(
                        'bulk_delete_aborted',
                        'Bulk delete aborted'
                    );
                }

                if($isDateOnAnyPayrollStatementAttendance){
                    $validator->errors()->add(
                        $attendance->employee->number . $attendance->date->toDateString(),
                        'Unable to delete ' . $attendance->employee->number . "'s " . $attendance->date->toDateString() . ' attendance, payroll generated.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'attendance_ids.required' => 'Attendance ids is required',
            'attendance_ids.array' => 'Attendance ids must be an array',
        ];
    }
}
