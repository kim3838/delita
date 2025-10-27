<?php

namespace App\Concrete\Imports;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\Imports\AttendanceImport;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Concrete\BaseImportConcrete;
use App\Exceptions\NotFoundException;
use App\Exports\BlankAttendanceTemplateExport;
use App\Http\Requests\Attendance\BaseAttendanceRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Shift;
use App\Traits\WorkPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class AttendanceImportConcrete extends BaseImportConcrete implements AttendanceImport
{
    use WorkPeriod;

    public function exportTemplate(): string
    {
        return BlankAttendanceTemplateExport::class;
    }

    /**
     * @throws NotFoundException
     */
    public function validateData($data, $companyId): array
    {
        $dataToImport = [];
        $employee = null;
        $shift = null;

        foreach ($data as $index => $row) {

            $validationErrors = [];

            if (empty($row['employee_number'])) {
                $validationErrors[] = 'Employee number is required.';
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

            if (empty($row['shift_code'])) {
                $validationErrors[] = 'Employee number is required.';
            } else {

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
            }

            /**
             * Validate attendance date
             **/
            $dateValidation = Validator::make($row,[
                'date' => 'required|date_format:Y-m-d',
            ]);
            $dateValidation->setCustomMessages([
                'date.required' => 'Date is required.',
                'date.date_format' => 'Date must match the format Y-m-d e.g.(2000-12-31).',
            ]);

            if($dateValidation->fails()){

                $validationErrors[] = $dateValidation->errors()->first();

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            } else {

                $date = Carbon::parse($row['date']);

                $row['date'] = $date->toDateString();
            }

            /**
             * Get the shift work day by attendance date
             **/
            $this->setAttendanceSchedule($date);

            /**
             * Validate if the shift is assigned to the employee within its shift assignment date
             **/
            $employeeShiftAssignment = $employee->shifts->where('id', $shift->id)->first()?->pivot;

            $row['require_lunch_time_in_and_out'] = $this->shiftRequireLunchOutAndIn;
            $row['is_flexible'] = $this->attendanceScheduleIsFlexible;

            if(empty($employeeShiftAssignment)){

                $validationErrors[] = 'Shift not assigned to employee.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            } else {

                if($employeeShiftAssignment->stated_shift_end_date){

                    /**
                     * Check if attendance date is between shift assignment start and end date
                     **/
                    if(!$date->between($employeeShiftAssignment->start_date, $employeeShiftAssignment->end_date)){
                        $validationErrors[] = 'Date is not in date range of employee shift assignment.';

                        $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                        continue;
                    }
                }

                /**
                 * If shift does not state shift end date
                 * Check if attendance date is lesser than shift assignment start
                 **/
                if(!$employeeShiftAssignment->stated_shift_end_date && $date->lt($employeeShiftAssignment->start_date)) {
                    $validationErrors[] = 'Date is not in date range of employee shift assignment.';

                    $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                    continue;
                }
            }

            /**
             * Attendance date should not be a day off
             **/
            if($this->attendanceScheduleIsDayOff){
                $validationErrors[] = 'Attendance date is a day off.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            $baseAttendanceRequest = new BaseAttendanceRequest();
            $baseRules = $baseAttendanceRequest->rules();
            $ruleMessages = $baseAttendanceRequest->messages();

            if($this->attendanceScheduleIsFlexible || !$this->shiftRequireLunchOutAndIn){
                $row['lunch_out'] = null;
                $row['lunch_in'] = null;
            }

            $timeValidation = Validator::make($row,[
                'first_in' => $baseRules['first_in'],
                ...(!$this->attendanceScheduleIsFlexible && $this->shiftRequireLunchOutAndIn ? [
                    'lunch_out' => $baseRules['lunch_out'],
                    'lunch_in' => $baseRules['lunch_in']
                ] : []),
                'last_out' => $baseRules['last_out'],
            ]);

            $timeValidation->setCustomMessages($ruleMessages);

            if($timeValidation->fails()){

                $validationErrors[] = $timeValidation->errors()->first();

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;

            } else {

                $firstIn = Carbon::parse($row['first_in']);
                $lunchOut = empty($row['lunch_out'])? null : Carbon::parse($row['lunch_out']);
                $lunchIn = empty($row['lunch_in'])? null : Carbon::parse($row['lunch_in']);
                $lastOut = Carbon::parse($row['last_out']);
            }

            /**
             * Get schedule for the attendance date
             * $schedule = $this->parseSchedule($this->attendanceSchedule, $date);
             **/

            /**
             * ?First in should be lesser than the shift work end
             * ?Last out should be greater than the shift work start
             **/

            /**
             * If Shift requires lunch out and in
             *
             * Lunch out should be between First in and Last out
             * Lunch in should be between Lunch out and Last out
             *
             **/
            if(!$this->attendanceScheduleIsFlexible && $this->shiftRequireLunchOutAndIn && !empty($lunchOut) && !empty($lunchIn)){

                if(!$lunchOut->between($firstIn, $lastOut)){
                    $validationErrors[] = 'Lunch out should be between of First in and Last out.';
                }

                if(!$lunchIn->between($lunchOut, $lastOut)){
                    $validationErrors[] = 'Lunch in should be between of Lunch out and Last out.';
                }

            }

            $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
        }

        return $dataToImport;
    }

    public function resolvedData($data, $companyId): array
    {
        $repository = App::make(AttendanceRepository::class);
        $attendanceSplitter = app(AttendanceSplitterInterface::class, ['company' => Company::query()->find($companyId)]);

        foreach ($data as $index => $row) {

            $save = [
                'employee_id' => $row['employee_id'],
                'shift_id' => $row['shift_id'],
                'date' => $row['date'],
                'first_in' => $row['first_in'],
                'lunch_out' => $row['lunch_out'],
                'lunch_in' => $row['lunch_in'],
                'last_out' => $row['last_out'],
            ];

            $existing = $repository->model()::query()
                ->where('employee_id', $row['employee_id'])
                ->where('shift_id', $row['shift_id'])
                ->where('date', $row['date'])
                ->first();

            $attendance = $existing
                ? $repository->update($existing->id, $save)
                : $repository->model()::create($save);

            $attendanceSplitter->generate($attendance);
        }

        return array_map(function ($row) {return $row['id'];}, $data);
    }
}
