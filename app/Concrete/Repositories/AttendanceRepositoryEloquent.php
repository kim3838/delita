<?php

namespace App\Concrete\Repositories;

use App\Blueprint\AttendanceSplitterInterface;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Attendance;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class AttendanceRepositoryEloquent extends BaseRepositoryEloquent implements AttendanceRepository
{
    public function model(): string
    {
        return Attendance::class;
    }

    public function update($id, $attributes)
    {
        $attendance = $this->model::where('ulid', $id)->firstOrFail();

        $attendanceSplitter = app(AttendanceSplitterInterface::class, ['company' => Company::query()->find($attributes['company_id'])]);

        $update = collect($attributes)->except(['company_id', 'employee_id', 'shift_id'])->toArray();

        $attendance->update($update);

        $attendanceSplitter->generate($attendance);

        return $attendance;
    }

    public function baseQueryBuilder($filters, $orders = null)
    {
        $employeeRepositoryFilter = clone $filters;

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'attendances.employee_id');
            })
            ->join('attendance_shift_details', 'attendance_shift_details.attendance_id', '=', 'attendances.id')
            ->when($filters->attendance_ulid ?? false, function ($builder, $value) {
                $builder->where('attendances.ulid', $value);
            })
            ->when((
                (isset($filters->date_from) && Carbon::createFromFormat('Y-m-d', $filters->date_from)) &&
                (isset($filters->date_to) && Carbon::createFromFormat('Y-m-d', $filters->date_to))
            ),function($builder) use ($filters){
                $builder->whereBetween('attendances.date', [$filters->date_from, $filters->date_to]);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "attendances.*",

                /**
                 * Shift
                 **/
                "attendance_shift_details.code AS shift_code",
                "attendance_shift_details.name AS shift_name",
                "attendance_shift_details.type AS shift_type",
                "attendance_shift_details.work_start_grace_time AS shift_work_start_grace_time",
                "attendance_shift_details.require_lunch_time_in_and_out AS shift_require_lunch_time_in_and_out",
                "attendance_shift_details.lunch_start_grace_time AS shift_lunch_start_grace_time",
                "attendance_shift_details.max_overtime AS shift_max_overtime",

                /**
                 * Shift Assignment
                 **/
                "attendance_shift_details.start_date AS shift_assignment_start_date",
                "attendance_shift_details.stated_shift_end_date AS shift_assignment_stated_shift_end_date",
                "attendance_shift_details.end_date AS shift_assignment_stated_shift_end_date",

                /**
                 * Shift Schedule
                 **/
                "attendance_shift_details.week_day AS shift_schedule_week_day",
                "attendance_shift_details.is_rest_day AS shift_schedule_is_rest_day",
                "attendance_shift_details.is_day_off AS shift_schedule_is_day_off",
                "attendance_shift_details.is_flexible AS shift_schedule_is_flexible",
                "attendance_shift_details.timezone AS shift_schedule_timezone",
                "attendance_shift_details.work_start AS shift_schedule_work_start",
                "attendance_shift_details.work_end AS shift_schedule_work_end",
                "attendance_shift_details.total_work_hours_with_breaks AS shift_schedule_total_work_hours_with_breaks",
                "attendance_shift_details.has_lunch_break AS shift_schedule_has_lunch_break",
                "attendance_shift_details.lunch_break_start AS shift_schedule_lunch_break_start",
                "attendance_shift_details.lunch_break_end AS shift_schedule_lunch_break_end",
                "attendance_shift_details.total_lunch_break_hours AS shift_schedule_total_lunch_break_hours",
            ]);

        return $queryBuilder;
    }

    public function show($id): Attendance
    {
        $filters = (object)[
            'attendance_ulid' => $id,
        ];

        $queryBuilder = $this->baseQueryBuilder($filters);

        $attendance = $queryBuilder->firstOrFail();

        return $this->hydrateItem($attendance);
    }

    public function list($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'attendances.date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new $this->model());
    }
}
