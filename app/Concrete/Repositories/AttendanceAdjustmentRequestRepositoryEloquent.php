<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceAdjustmentRequestRepository;
use App\Blueprint\Repositories\AttendanceRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\AttendanceAdjustmentRequest;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class AttendanceAdjustmentRequestRepositoryEloquent extends BaseRepositoryEloquent implements AttendanceAdjustmentRequestRepository
{
    public function model(): string
    {
        return AttendanceAdjustmentRequest::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $attendanceRepositoryFilter = clone $filters;

        if(isset($attendanceRepositoryFilter->attendance_date_from)){
            $attendanceRepositoryFilter->date_from = $attendanceRepositoryFilter->attendance_date_from;
        }
        if(isset($attendanceRepositoryFilter->attendance_date_to)){
            $attendanceRepositoryFilter->date_to = $attendanceRepositoryFilter->attendance_date_to;
        }

        unset($filters->attendance_date_from);
        unset($filters->attendance_date_to);

        $attendanceQueryBuilder = App::make(AttendanceRepository::class)->baseQueryBuilder($attendanceRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($attendanceQueryBuilder, 'attendance_sub', function ($join) {
                $join->on('attendance_sub.id', '=', 'attendance_adjustment_requests.attendance_id');
            })
            ->join('companies', 'attendance_sub.employee_company_id', '=', 'companies.id')
            ->when(!empty($filters->requested_by_ids) && is_array($filters->requested_by_ids), function ($builder) use ($filters) {
                $builder->whereIn('attendance_adjustment_requests.requested_by', $filters->requested_by_ids);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                "attendance_sub.employee_company_id AS employee_company_id",

                "attendance_sub.ulid AS attendance_ulid",
                "attendance_sub.employee_id AS attendance_employee_id",
                "attendance_sub.shift_id AS attendance_shift_id",
                "attendance_sub.date AS attendance_date",
                "attendance_sub.first_in AS attendance_first_in",
                "attendance_sub.lunch_out AS attendance_lunch_out",
                "attendance_sub.lunch_in AS attendance_lunch_in",
                "attendance_sub.last_out AS attendance_last_out",
                "attendance_sub.status AS attendance_status",

                /**
                 * Shift
                 **/
                "attendance_sub.shift_code AS attendance_shift_code",
                "attendance_sub.shift_name AS attendance_shift_name",
                "attendance_sub.shift_type AS attendance_shift_type",
                "attendance_sub.shift_holiday_policy AS attendance_shift_holiday_policy",
                "attendance_sub.shift_work_start_grace_time AS attendance_shift_work_start_grace_time",
                "attendance_sub.shift_require_lunch_time_in_and_out AS attendance_shift_require_lunch_time_in_and_out",
                "attendance_sub.shift_lunch_start_grace_time AS attendance_shift_lunch_start_grace_time",
                "attendance_sub.shift_max_overtime AS attendance_shift_max_overtime",

                /**
                 * Shift Assignment
                 **/
                "attendance_sub.shift_assignment_start_date AS attendance_shift_assignment_start_date",
                "attendance_sub.shift_assignment_stated_shift_end_date AS attendance_shift_assignment_stated_shift_end_date",
                "attendance_sub.shift_assignment_end_date AS attendance_shift_assignment_end_date",

                /**
                 * Shift Schedule
                 **/
                "attendance_sub.shift_schedule_week_day AS attendance_shift_schedule_week_day",
                "attendance_sub.shift_schedule_is_rest_day AS attendance_shift_schedule_is_rest_day",
                "attendance_sub.shift_schedule_is_day_off AS attendance_shift_schedule_is_day_off",
                "attendance_sub.shift_schedule_is_flexible AS attendance_shift_schedule_is_flexible",
                "attendance_sub.shift_schedule_timezone AS attendance_shift_schedule_timezone",
                "attendance_sub.shift_schedule_work_start AS attendance_shift_schedule_work_start",
                "attendance_sub.shift_schedule_work_end AS attendance_shift_schedule_work_end",
                "attendance_sub.shift_schedule_total_work_hours_with_breaks AS attendance_shift_schedule_total_work_hours_with_breaks",
                "attendance_sub.shift_schedule_has_lunch_break AS attendance_shift_schedule_has_lunch_break",
                "attendance_sub.shift_schedule_lunch_break_start AS attendance_shift_schedule_lunch_break_start",
                "attendance_sub.shift_schedule_lunch_break_end AS attendance_shift_schedule_lunch_break_end",
                "attendance_sub.shift_schedule_total_lunch_break_hours AS attendance_shift_schedule_total_lunch_break_hours",

                /**
                 * Attendance Adjustment
                 **/
                'attendance_adjustment_requests.id AS id',
                'attendance_adjustment_requests.requested_by AS requested_by',
                DB::raw("CONVERT_TZ(attendance_adjustment_requests.date_requested, 'UTC', companies.timezone) AS date_requested"),
                'attendance_adjustment_requests.attendance_id AS attendance_id',
                'attendance_adjustment_requests.first_in AS first_in',
                'attendance_adjustment_requests.lunch_out AS lunch_out',
                'attendance_adjustment_requests.lunch_in AS lunch_in',
                'attendance_adjustment_requests.last_out AS last_out',
                'attendance_adjustment_requests.reason AS reason',
            ]);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'attendance_sub.employee_number', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
