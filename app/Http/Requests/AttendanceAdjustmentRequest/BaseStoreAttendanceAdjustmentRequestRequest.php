<?php

namespace App\Http\Requests\AttendanceAdjustmentRequest;

use App\Http\Requests\Attendance\ImportAttendance;
use App\Models\Attendance;
use App\Models\AttendanceAdjustmentRequest;
use App\Models\Shift;
use App\Traits\HasApproval;
use App\Traits\WorkPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;

class BaseStoreAttendanceAdjustmentRequestRequest extends ImportAttendance
{
    use WorkPeriod, HasApproval;

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

                    $date = Carbon::parse($attendance->date);

                    if (!$attendance) {
                        $fail('Attendance not found');
                    }

                    /**
                     * Validate if request has approvers
                     **/
                    $modelAlias = Relation::getMorphAlias(AttendanceAdjustmentRequest::class);

                    /**
                     * Validate if there are approvers
                     **/
                    $approversArray = $this->getRequestableApprovers($modelAlias, $attendance->id, $companyId, $this->user()->id);

                    if(empty($approversArray)){
                        $fail('No approvers found for this request.');
                    }

                    /**
                     * Validate if there are any changes
                     **/
                    if($this->attendanceIsClean($attendance)){
                        $fail('No changes found');
                    }

                    $shift = Shift::query()->find($attendance->shift_id);

                    if(!$shift instanceof Shift){
                        $fail('Shift not found');
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
            ],
            'remarks' => 'nullable|string|max:255',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'company_id.required' => 'Company is required',
            'remarks.max' => 'Remarks must not exceed 255 characters'
        ]);
    }
}
