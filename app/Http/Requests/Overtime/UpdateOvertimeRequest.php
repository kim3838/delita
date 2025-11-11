<?php

namespace App\Http\Requests\Overtime;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Traits\WorkPeriod;
use Carbon\Carbon;

class UpdateOvertimeRequest extends BaseOvertimeRequest
{
    use WorkPeriod;

    public function authorize(): bool
    {
        $overtime = Overtime::query()->where('ulid', $this->route('overtimeUlid'))->firstOrFail();

        return $this->user()->can('update', $overtime);
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
            'date' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {

                    $date = Carbon::parse($value);
                    $employeeId = $this->input('employee_id');
                    $overtimeStart = Carbon::parse($this->input('start'));
                    $overtimeEnd = Carbon::parse($this->input('end'));
                    $overtimeUlId = $this->route('overtimeUlid');

                    $attendance = Attendance::query()
                        ->where('employee_id', $employeeId)
                        ->where('date', $value)
                        ->first();

                    if (!$attendance) {
                        $fail('Attendance not found');
                    } else {

                        $this->setShift($attendance->shift_id);
                        $this->setAttendanceSchedule($date);

                        /**
                         * Validate existing overtime
                         **/
                        if($overtimeUlId){

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

                                /**
                                 * Overtime end should not be greater than attendance last out
                                 **/
                                if($overtimeEnd->gt($attendance->last_out)){
                                    $fail('Overtime end should not be greater than attendance last out');
                                }

                                /**
                                 * Overtime start should not be greater than or equal attendance last out
                                 **/
                                if($overtimeStart->gte($attendance->last_out)){
                                    $fail('Overtime start should be lesser than attendance last out');
                                }

                                /**
                                 * Overtime end should not be lesser than the schedule end
                                 **/
                                if($overtimeEnd->lt($schedule['work_end'])){
                                    $fail('Overtime end should not be lesser than the schedule end');
                                }

                                /**
                                 * Overtime start should not be lesser than the schedule end
                                 **/
                                if($overtimeStart->lt($schedule['work_end'])){
                                    $fail('Overtime start should not be lesser than the schedule end');
                                }
                            }
                        }
                    }
                }
            ],
        ]);
    }
}
