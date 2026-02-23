<?php

namespace App\Http\Requests\Attendance;

use App\Blueprint\PayrollServiceInterface;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Shift;
use App\Traits\WorkPeriod;
use Carbon\Carbon;

class UpdateAttendanceRequest extends ImportAttendance
{
    use WorkPeriod;

    public function authorize(): bool
    {
        $attendance = Attendance::query()->where('ulid', $this->route('attendanceUlid'))->firstOrFail();

        return $this->user()->can('update', $attendance);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'company_id' => 'required|numeric|integer',
            'employee_id' => [
                'required',
                'numeric',
                'integer',
                function ($attribute, $value, $fail) {

                    $employee = Employee::query()->find($value);

                    if (!$employee) {
                        $fail('Employee not found');
                    }
                }
            ],
            'shift_id' => [
                'required',
                'numeric',
                'integer',
                function ($attribute, $value, $fail) {

                    $date = Carbon::parse($this->input('date'));

                    $shift = Shift::query()->find($value);

                    if (!$shift) {
                        $fail('Shift not found');
                    }

                    $attendance = Attendance::query()->where('ulid', $this->route('attendanceUlid'))->first();

                    if (!$attendance) {
                        $fail('Attendance not found');
                    } else {

                        /**
                         * Validate if there are any changes
                         **/
                        if($this->attendanceIsClean($attendance)){
                            $fail('No changes found');
                        }

                        $this->setShift($shift);

                        /**
                         * After setting up shift,
                         * Get the shift work day by attendance date
                         **/
                        $this->setAttendanceSchedule($date);

                        /**
                         * Validate attendance shift details if still match the current shift and schedule settings
                         * */
                        list(
                            $currentShiftAndAttendanceShiftStillTheSame,
                            $currentShiftScheduleAndAttendanceShiftScheduleStillTheSame
                        ) = $this->validateAttendanceShiftDetails(
                            $this->shift,
                            $this->attendanceSchedule,
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
                            $schedule = $this->parseSchedule($this->attendanceSchedule, $date);

                            $firstIn = Carbon::parse($this->input('first_in'));
                            $lunchOut = empty($this->input('lunch_out'))? null : Carbon::parse($this->input('lunch_out'));
                            $lunchIn = empty($this->input('lunch_in'))? null : Carbon::parse($this->input('lunch_in'));
                            $lastOut = Carbon::parse($this->input('last_out'));

                            $attendanceValidationErrors = $this->validateAttendance(
                                $firstIn, $lunchOut, $lunchIn, $lastOut,
                                $schedule,
                                !$this->attendanceScheduleIsFlexible && $this->shiftRequireLunchOutAndIn && $this->attendanceScheduleHasLunchBreak
                            );

                            foreach($attendanceValidationErrors as $error){
                                $fail($error);
                            }
                        }
                    }
                }
            ],
            'date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $company = Company::query()->find($this->input('company_id'));
            $employee = Employee::query()->find($this->input('employee_id'));
            $date = Carbon::parse($this->input('date'));

            $payrollService = app(PayrollServiceInterface::class, [$company]);

            $isDateOnAnyPayrollStatementAttendance = $payrollService->isDateOnAnyPayrollStatementAttendance($employee, $date);

            if (!empty($employee) && !empty($company) && $isDateOnAnyPayrollStatementAttendance) {

                $validator->errors()->add(
                    'date',
                    'Unable to update, payroll generated.'
                );
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'company_id.required' => 'Company is required',
            'employee_id.required' => 'Employee is required',
            'shift_id.required' => 'Attendance shift not found. Please re-import attendance with existing shift assigned to employee',
        ]);
    }
}
