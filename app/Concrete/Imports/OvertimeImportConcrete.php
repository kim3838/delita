<?php

namespace App\Concrete\Imports;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\Imports\OvertimeImport;
use App\Blueprint\Repositories\OvertimeRepository;
use App\Concrete\BaseImportConcrete;
use App\Exceptions\UnexpectedException;
use App\Exports\BlankOvertimeTemplateExport;
use App\Http\Requests\Overtime\ImportOvertime;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Shift;
use App\Traits\WorkPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class OvertimeImportConcrete extends BaseImportConcrete implements OvertimeImport
{
    use WorkPeriod;

    public function model(): string
    {
        return Overtime::class;
    }

    public function exportTemplate(): string
    {
        return BlankOvertimeTemplateExport::class;
    }

    /**
     * @throws UnexpectedException
     */
    public function validateData($data, $companyId):array
    {
        $dataToImport = [];
        $employee = null;

        foreach ($data as $index => $row) {

            $validationErrors = [];

            if (empty($row['employee_number'])) {
                $validationErrors[] = 'Employee number is required.';
                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            } else {

                $employee = Employee::query()
                    ->where('company_id', $companyId)
                    ->where('number', $row['employee_number'])
                    ->first();

                if (empty($employee)) {
                    $validationErrors[] = 'Employee not found.';

                    $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                    continue;
                } else {
                    $row['employee_id'] = $employee->id;
                }
            }

            $shift = Shift::query()
                ->where('company_id', $companyId)
                ->where('code', $row['shift_code'])
                ->first();

            if (empty($shift)) {
                $validationErrors[] = 'Shift not found.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            } else {

                $row['shift_id'] = $shift->id;
                $this->setShift($shift);
            }

            /**
             * Validate attendance date
             **/
            $attendanceDateValidation = Validator::make($row,[
                'attendance_date' => 'required|date_format:Y-m-d',
            ]);
            $attendanceDateValidation->setCustomMessages([
                'attendance_date.required' => 'Attendance date is required.',
                'attendance_date.date_format' => 'Attendance date must match the format Y-m-d e.g.(2000-12-31).',
            ]);

            if($attendanceDateValidation->fails()){

                $validationErrors[] = $attendanceDateValidation->errors()->first();

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            } else {

                $date = Carbon::parse($row['attendance_date']);

                $row['attendance_date'] = $date->toDateString();
            }

            $attendance = Attendance::query()
                ->where('employee_id', $row['employee_id'])
                ->where('shift_id', $row['shift_id'])
                ->where('date', $row['attendance_date'])
                ->first();

            if(empty($attendance)){

                $validationErrors[] = 'Attendance not found.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            } else {

                $row['attendance_id'] = $attendance->id;
            }

            if(!empty($attendance->overtime)){

                $validationErrors[] = 'Attendance already has overtime.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;

            } else {

                if(!$this->isActionAuthorized('create', $this->model)){

                    $validationErrors[] = 'Unauthorized create.';

                    $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                    continue;
                }
            }

            /**
             * Get the shift work day by attendance date
             **/
            $this->setAttendanceSchedule($date);

            if($this->attendanceScheduleIsFlexible){

                $validationErrors[] = 'Overtime cannot be applied to flexible shift schedule.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;

            } else if((float)$shift->max_overtime <= 0){

                $validationErrors[] = 'Shift schedule has no overtime.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            $lastAttendanceBreakdown = $attendance->details->sortByDesc('order')->first();
            $lastAttendanceBreakdownSplitEnd = Carbon::parse($lastAttendanceBreakdown->date->toDateString() . ' ' . $lastAttendanceBreakdown->split_end);

            if($attendance->last_out->lte($lastAttendanceBreakdownSplitEnd)){

                $validationErrors[] = 'Unable to create overtime if last out does not exceed schedule work end.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            $importOvertime = new ImportOvertime();
            $importOvertimeRules = $importOvertime->rules();
            $importOvertimeRulesMessages = $importOvertime->messages();

            $timeValidation = Validator::make($row,[
                'overtime_start' => $importOvertimeRules['start'],
                'overtime_end' => $importOvertimeRules['end'],
            ]);

            $timeValidation->setCustomMessages([
                'overtime_start.required' => $importOvertimeRulesMessages['start.required'],
                'overtime_start.date_format' => $importOvertimeRulesMessages['start.date_format'],
                'overtime_end.required' => $importOvertimeRulesMessages['end.required'],
                'overtime_end.date_format' => $importOvertimeRulesMessages['end.date_format'],
            ]);

            if($timeValidation->fails()){

                $validationErrors[] = $timeValidation->errors()->first();

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;

            }

            /**
             * Get the schedule for the attendance date
             **/
            $schedule = $this->parseSchedule($this->attendanceSchedule, $date);

            $overtimeStart = Carbon::parse($row['overtime_start']);
            $overtimeEnd = Carbon::parse($row['overtime_end']);

            $overtimeValidatedErrors = $importOvertime->validateOvertime($attendance, $overtimeStart, $overtimeEnd, $schedule);

            foreach($overtimeValidatedErrors as $error){
                $validationErrors[] = $error;
            }

            $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
        }

        return $dataToImport;
    }

    public function resolvedData($data, $companyId): array
    {
        $repository = App::make(OvertimeRepository::class);
        $attendanceSplitter = App::make(AttendanceSplitterInterface::class, [Company::query()->find($companyId)]);

        foreach ($data as $index => $row) {

            $attendance = Attendance::query()->findOrFail($row['attendance_id']);

            $save = [
                'attendance_id' => $row['attendance_id'],
                'start' => $row['overtime_start'],
                'end' => $row['overtime_end'],
            ];

            if(empty($attendance->overtime)){
                $repository->store($save, $attendanceSplitter);

            } else {
                $repository->update($attendance->overtime->ulid, $save, $attendanceSplitter);
            }
        }

        return array_map(function ($row) {return $row['id'];}, $data);
    }
}
