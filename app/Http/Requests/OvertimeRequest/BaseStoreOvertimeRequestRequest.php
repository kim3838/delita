<?php

namespace App\Http\Requests\OvertimeRequest;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\WorkPeriodServiceInterface;
use App\Http\Requests\Overtime\ImportOvertime;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\OvertimeRequest;
use App\Models\Shift;
use App\Traits\HasApproval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;

class BaseStoreOvertimeRequestRequest extends ImportOvertime
{
    use HasApproval;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'company_id' => 'required|numeric|integer|exists:companies,id',
            'attendance_id' => [
                'required',
                'numeric',
                'integer',
                function ($attribute, $value, $fail) {

                    $companyId = $this->input('company_id');
                    $attendance = Attendance::query()->find($value);

                    $overtimeStart = Carbon::parse($this->input('start'));
                    $overtimeEnd = Carbon::parse($this->input('end'));

                    $date = Carbon::parse($attendance->date);

                    if (!$attendance) {
                        $fail('Attendance not found');
                    }

                    /**
                     * Validate if request has approvers
                     **/
                    $modelAlias = Relation::getMorphAlias(OvertimeRequest::class);

                    /**
                     * Validate if there are approvers
                     **/
                    $approversArray = $this->getRequestableApprovers($modelAlias, $attendance->id, $companyId, $this->user()->id);

                    if(empty($approversArray)){
                        $fail('No approvers found for this request.');
                    }

                    $workPeriodService = app(WorkPeriodServiceInterface::class);

                    $shift = Shift::query()->find($attendance->shift_id);

                    if(!$shift instanceof Shift){
                        $fail('Shift not found');
                    }

                    $workPeriodService->setShift($shift);

                    /**
                     * After setting up shift,
                     * Get the shift work day by attendance date
                     **/
                    $workPeriodService->setAttendanceSchedule($date);

                    /**
                     * Validate attendance shift details if still match the current shift and schedule settings
                     * */
                    list(
                        $currentShiftAndAttendanceShiftStillTheSame,
                        $currentShiftScheduleAndAttendanceShiftScheduleStillTheSame
                    ) = $workPeriodService->validateAttendanceShiftDetails(
                        $workPeriodService->shift,
                        $workPeriodService->attendanceSchedule,
                        $attendance->shiftDetail->toArray(),
                        $attendance->shiftDetail->toArray()
                    );

                    if(!$currentShiftAndAttendanceShiftStillTheSame){
                        $fail('Shift settings have changed. Please re-import attendance');
                    } else if(!$currentShiftScheduleAndAttendanceShiftScheduleStillTheSame){
                        $fail('Shift schedule settings have changed. Please re-import attendance');
                    } else {

                        /**
                         * Get the schedule for the attendance date
                         **/
                        $schedule = $workPeriodService->parseSchedule($workPeriodService->attendanceSchedule, $date);

                        $overtimeValidatedErrors = $this->validateOvertime($attendance, $overtimeStart, $overtimeEnd, $schedule);

                        foreach($overtimeValidatedErrors as $error){
                            $fail($error);
                        }
                    }
                }
            ],
            'remarks' => 'nullable|string|max:255',
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $company = Company::query()->find($this->input('company_id'));
            $attendance = Attendance::query()->find($this->input('attendance_id'));

            $payrollService = app(PayrollServiceInterface::class, [$company]);

            $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($attendance->employee, $attendance->date);

            if ($isDateOnAnyPayrollStatementAttendance) {

                $validator->errors()->add(
                    'date',
                    'Unable to submit, payroll generated.'
                );
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'company_id.required' => 'Company is required',
            'remarks.max' => 'Remarks must not exceed 255 characters'
        ]);
    }
}
