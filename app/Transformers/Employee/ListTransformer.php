<?php

namespace App\Transformers\Employee;

use App\Blueprint\Repositories\EmployeeShiftRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Blueprint\Repositories\ShiftRepository;
use App\Blueprint\Repositories\ShiftScheduleRepository;
use App\Blueprint\Repositories\UserRepository;
use App\Enums\DepartmentEmployeeAssignmentType;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Transformers\EmployeeShift\ItemTransformer as EmployeeShiftItemTransformer;
use App\Transformers\EmploymentProfile\CurrentEmploymentProfileTransformer;
use App\Transformers\PayFrequency\ItemTransformer as PayFrequencyItemTransformer;
use App\Transformers\Shift\ItemTransformer as ShiftItemTransformer;
use App\Transformers\ShiftSchedule\ListTransformer as ShiftScheduleListTransformer;
use App\Transformers\User\BasicTransformer as UserBasicTransformer;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Employee $employee): array
    {
        $employeeUserHydrated = App::make(UserRepository::class)->hydrateItem([
            'id' => $employee->user_id,
            'name' => $employee->user_name,
            'email' => $employee->user_email,
            'status' => $employee->user_status,
            'email_verified_at' => $employee->user_email_verified_at,
            'timezone' => $employee->user_timezone
        ]);

        $employeeUser = Fractal::item($employeeUserHydrated, UserBasicTransformer::class);

        $currentEmploymentProfileHydrated = App::make(EmploymentProfileRepository::class)->hydrateItem([
            'id' => $employee->employment_profile_id,
            'employee_id' => $employee->employment_profile_employee_id,
            'is_active' => $employee->employment_status_active,
            'status' => $employee->current_employment_status,
            'employment_type' => $employee->current_employment_type,
            'start_date' => $employee->current_employment_start_date,
            'end_of_service_type' => $employee->end_of_service_type,
            'end_date' => $employee->current_employment_end_date,
        ]);

        $currentEmploymentProfile = Fractal::item($currentEmploymentProfileHydrated, CurrentEmploymentProfileTransformer::class);

        $hasCurrentShift = $employee->current_employee_shift_id ?? null;

        if($hasCurrentShift){

            $currentEmployeeShiftHydrated = App::make(EmployeeShiftRepository::class)->hydrateItem([
                'start_date' => $employee->current_shift_start_date,
                'stated_shift_end_date' => $employee->current_shift_stated_shift_end_date,
                'end_date' => $employee->current_shift_end_date,
            ]);

            $currentEmployeeShiftAssignment = Fractal::item($currentEmployeeShiftHydrated, EmployeeShiftItemTransformer::class);

            $currentShift = App::make(ShiftRepository::class)->model()::query()->find($employee->current_shift_id);
            $currentShiftScheduleFilters = (object)[
                'shift_id' => $currentShift->id
            ];
            $currentShiftSchedules = App::make(ShiftScheduleRepository::class)->list($currentShiftScheduleFilters);
            $currentShiftSchedules = Fractal::collection($currentShiftSchedules, ShiftScheduleListTransformer::class)['data'];

            $currentShift = $currentShift ? Fractal::item($currentShift, ShiftItemTransformer::class) : $currentShift;
        }

        $hasUpcomingShift = $employee->upcoming_employee_shift_id ?? null;

        if($hasUpcomingShift){

            $upcomingEmployeeShiftHydrated = App::make(EmployeeShiftRepository::class)->hydrateItem([
                'start_date' => $employee->upcoming_shift_start_date,
                'stated_shift_end_date' => $employee->upcoming_shift_stated_shift_end_date,
                'end_date' => $employee->upcoming_shift_end_date,
            ]);

            $upcomingEmployeeShiftAssignment = Fractal::item($upcomingEmployeeShiftHydrated, EmployeeShiftItemTransformer::class);

            $upcomingShift = App::make(ShiftRepository::class)->model()::query()->find($employee->upcoming_shift_id);
            $upcomingShiftScheduleFilters = (object)[
                'shift_id' => $upcomingShift->id
            ];
            $upcomingShiftSchedules = App::make(ShiftScheduleRepository::class)->list($upcomingShiftScheduleFilters);
            $upcomingShiftSchedules = Fractal::collection($upcomingShiftSchedules, ShiftScheduleListTransformer::class)['data'];

            $upcomingShift = $upcomingShift ? Fractal::item($upcomingShift, ShiftItemTransformer::class) : $upcomingShift;
        }

        $payrollGroup = $employee->payFrequency ? Fractal::item($employee->payFrequency, PayFrequencyItemTransformer::class) : null;

        return [
            'id' => $employee->id,
            'ulid' => $employee->ulid,
            'row_number' => $employee->row_number,
            'number' => $employee->number,
            'full_name' => $employee->full_name,
            'gender' => $employee->gender?->toArray(),
            'marital_status' => $employee->marital_status?->toArray(),
            'birth_date' => $employee->birth_date?->toDateString(),
            'birth_date_readable' => $employee->birth_date?->format('M j, Y') ?? '--',
            'department' => $employee->department_employee_id
                ? [
                    'name' => $employee->department_name,
                    'assignment_type' => DepartmentEmployeeAssignmentType::tryFrom($employee->department_assignment_type)?->toArray()
                ] : null,
            'designation' => $employee->designation_id
                ? ['name' => $employee->designation_name]
                : null,
            'manager' => $employee->manager,
            'pay_frequency_id' => $employee->pay_frequency_id,
            'payroll_group' => $payrollGroup,
            'contact' => $employee->contact,
            'current_employment_profile' => $currentEmploymentProfile,
            'user' => $employeeUser,
            'has_current_shift' => boolval($hasCurrentShift),
            'current_shift' => [
                ...($hasCurrentShift ? [
                    'shift' => $currentShift,
                    'shift_schedules' => $currentShiftSchedules,
                    'shift_assignment' => $currentEmployeeShiftAssignment
                ] : [])
            ],
            'has_upcoming_shift' => boolval($hasUpcomingShift),
            'upcoming_shift' => [
                ...($hasUpcomingShift ? [
                    'shift' => $upcomingShift,
                    'shift_schedules' => $upcomingShiftSchedules,
                    'shift_assignment' => $upcomingEmployeeShiftAssignment
                ] : [])
            ]
        ];
    }
}
