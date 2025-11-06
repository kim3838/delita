<?php

namespace App\Concrete\Imports;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\Imports\AttendanceImport;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Concrete\BaseImportConcrete;
use App\Enums\ShiftHolidayPolicy;
use App\Exceptions\UnexpectedException;
use App\Exports\BlankAttendanceTemplateExport;
use App\Facades\Fractal;
use App\Http\Requests\Attendance\ImportAttendance;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Shift;
use App\Traits\WorkPeriod;
use App\Transformers\EmployeeShift\PatchableTransformer as EmployeeShiftPatchableTransformer;
use App\Transformers\Shift\PatchableTransformer as ShiftPatchableTransformer;
use App\Transformers\ShiftSchedule\PatchableTransformer as ShiftSchedulePatchableTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class AttendanceImportConcrete extends BaseImportConcrete implements AttendanceImport
{
    use WorkPeriod;

    public function model(): string
    {
        return Attendance::class;
    }

    public function exportTemplate(): string
    {
        return BlankAttendanceTemplateExport::class;
    }

    /**
     *
     * @throws UnexpectedException
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
             * Validate authorization on create if not exists and update if exists
             **/
            $existing = $this->model::where('employee_id', $row['employee_id'])
                ->where('shift_id', $row['shift_id'])
                ->where('date', $row['date'])
                ->first();

            if($existing){

                if(!$this->isActionAuthorized('update', $existing)){

                    $validationErrors[] = 'Unauthorized update.';

                    $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                    continue;
                }

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

            /**
             * Attendance date should not be a day off
             **/
            if($this->attendanceScheduleIsDayOff){
                $validationErrors[] = 'Attendance date is a day off.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            /**
             * Get the schedule for the attendance date
             **/
            $schedule = $this->parseSchedule($this->attendanceSchedule, $date);

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
             * Check if the attendance's date is a holiday
             * And shift holiday policy if its day off
             **/
            $isAttendanceDateIsHoliday = !empty($this->getCompanyHolidayByDate($date->toDateString(), $companyId));
            $shiftHolidayPolicyIsDayOff = $this->shiftHolidayPolicy == ShiftHolidayPolicy::DAY_OFF;

            /**
             * If the attendance's date is a holiday, and shift holiday policy is a day off, attendance is not needed
             **/
            if($isAttendanceDateIsHoliday && $shiftHolidayPolicyIsDayOff){

                $validationErrors[] = 'Shift does not required attendance on holiday.';

                $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
                continue;
            }

            $importAttendance = new ImportAttendance();
            $importAttendanceRules = $importAttendance->rules();
            $importAttendanceRulesMessages = $importAttendance->messages();

            if($this->attendanceScheduleIsFlexible || !$this->shiftRequireLunchOutAndIn || !$this->attendanceScheduleHasLunchBreak){
                $row['lunch_out'] = null;
                $row['lunch_in'] = null;
            }

            $timeValidation = Validator::make($row,[
                'first_in' => $importAttendanceRules['first_in'],
                ...(!$this->attendanceScheduleIsFlexible && $this->shiftRequireLunchOutAndIn && $this->attendanceScheduleHasLunchBreak ? [
                    'lunch_out' => $importAttendanceRules['lunch_out'],
                    'lunch_in' => $importAttendanceRules['lunch_in']
                ] : []),
                'last_out' => $importAttendanceRules['last_out'],
            ]);

            $timeValidation->setCustomMessages($importAttendanceRulesMessages);

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

            $attendanceValidationErrors = $importAttendance->validateAttendance(
                $firstIn, $lunchOut, $lunchIn, $lastOut,
                $schedule,
                !$this->attendanceScheduleIsFlexible && $this->shiftRequireLunchOutAndIn && $this->attendanceScheduleHasLunchBreak
            );

            foreach($attendanceValidationErrors as $error){
                $validationErrors[] = $error;
            }

            $this->resolveValidatedRow($row, $validationErrors, $dataToImport);
        }

        return $dataToImport;
    }

    /**
     *
     * @throws UnexpectedException
     */
    public function resolvedData($data, $companyId): array
    {
        $repository = App::make(AttendanceRepository::class);
        $attendanceSplitter = App::make(AttendanceSplitterInterface::class, [Company::query()->find($companyId)]);
        $shiftSchedule = App::make(ShiftScheduleRepository::class);

        foreach ($data as $index => $row) {

            $save = [
                'company_id' => $companyId,
                'employee_id' => $row['employee_id'],
                'shift_id' => $row['shift_id'],
                'date' => $row['date'],
                'first_in' => $row['first_in'],
                'lunch_out' => $row['lunch_out'],
                'lunch_in' => $row['lunch_in'],
                'last_out' => $row['last_out'],
            ];

            $this->setShift($row['shift_id']);
            $this->setAttendanceSchedule(Carbon::parse($row['date']));

            $shiftAssignment = Employee::query()->find($row['employee_id'])->shifts->where('id', $this->shift->id)->first()?->pivot;

            if(empty($shiftAssignment)){
                throw new UnexpectedException("Attendance shift assignment not found: C.AttendanceImportConcrete [" . __LINE__ . "]");
            }

            $shiftScheduleHydrated = $shiftSchedule->hydrateItem($this->attendanceSchedule);

            $shiftDetail = [
                ...Fractal::item($shiftAssignment, EmployeeShiftPatchableTransformer::class),
                ...Fractal::item($this->shift, ShiftPatchableTransformer::class),
                ...Fractal::item($shiftScheduleHydrated, ShiftSchedulePatchableTransformer::class)
            ];

            $existing = $repository->model()::query()
                ->where('employee_id', $row['employee_id'])
                ->where('shift_id', $row['shift_id'])
                ->where('date', $row['date'])
                ->first();

            $attendance = $existing ?: $repository->model()::create($save);

            if($existing){
                //Delete existing overtime
                $attendance->overtime?->delete();

                //Attendance splitter is also called on attendance repository update
                $repository->update($existing->ulid, $save, $attendanceSplitter);

            } else {
                //Generate attendance splitter on newly created attendance
                $attendanceSplitter->generate($attendance);
            }

            //Update attendance shift details every created or modified
            $attendance->shiftDetail()->delete();

            $attendance->shiftDetail()->create($shiftDetail);
        }

        return array_map(function ($row) {return $row['id'];}, $data);
    }
}
