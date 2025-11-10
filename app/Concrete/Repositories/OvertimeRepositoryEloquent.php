<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\OvertimeRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Overtime;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class OvertimeRepositoryEloquent extends BaseRepositoryEloquent implements OvertimeRepository
{
    public function model(): string
    {
        return Overtime::class;
    }

    public function baseQueryBuilder($filters, $orders = null)
    {
        $attendanceRepositoryFilter = clone $filters;

        $attendanceQueryBuilder = App::make(AttendanceRepository::class)->baseQueryBuilder($attendanceRepositoryFilter, []);

        $queryBuilder = $this->model->getQuery()
            ->joinSub($attendanceQueryBuilder, 'attendance_sub', function ($join) {
                $join->on('attendance_sub.id', '=', 'overtimes.attendance_id');
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),

                "overtimes.id AS id",
                "overtimes.ulid AS ulid",
                "overtimes.attendance_id AS attendance_id",
                "overtimes.start AS start",
                "overtimes.end AS end",
                "overtimes.duration AS duration",

                /**
                 * Attendance
                 **/
                "attendance_sub.ulid AS attendance_ulid",
                "attendance_sub.employee_id AS attendance_employee_id",

                /**
                 * Shift
                 **/
                "attendance_sub.shift_max_overtime AS attendance_shift_max_overtime",

                /**
                 * Shift Schedule
                 **/
                "attendance_sub.shift_schedule_week_day AS attendance_shift_schedule_week_day",
                "attendance_sub.shift_schedule_work_start AS attendance_shift_schedule_work_start",
                "attendance_sub.shift_schedule_work_end AS attendance_shift_schedule_work_end",
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function list($filters)
    {
        $orders = [
            ['field' => 'attendance_sub.employee_number', 'direction' => 'ASC'],
            ['field' => 'date', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, new $this->model());
    }
}
