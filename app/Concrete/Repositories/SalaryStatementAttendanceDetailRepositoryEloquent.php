<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceDetailRepository;
use App\Blueprint\Repositories\SalaryStatementAttendanceDetailRepository;
use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\SalaryStatementAttendanceDetail;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SalaryStatementAttendanceDetailRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementAttendanceDetailRepository
{
    public function model(): string
    {
        return SalaryStatementAttendanceDetail::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $salaryStatementAttendanceRepositoryFilter = clone $filters;
        $attendanceDetailRepositoryFilter = (object)[];

        $salaryStatementAttendanceQueryBuilder = App::make(SalaryStatementAttendanceRepository::class)->baseQueryBuilder($salaryStatementAttendanceRepositoryFilter);
        $attendanceDetailQueryBuilder = App::make(AttendanceDetailRepository::class)->baseQueryBuilder($attendanceDetailRepositoryFilter);

        $payableNonAttendanceQueryBuilder = $this->model::query()->getQuery()
            ->joinSub($salaryStatementAttendanceQueryBuilder, 'salary_statement_attendance_sub', function ($join) use ($filters) {
                $join->on('salary_statement_attendance_sub.id', '=', 'salary_statement_attendance_details.salary_statement_attendance_id')
                    ->when(!empty($filters->salary_statement_attendance_ids) && is_array($filters->salary_statement_attendance_ids), function ($builder) use ($filters) {
                        $builder->whereIn(DB::raw("salary_statement_attendance_details.salary_statement_attendance_id"), $filters->salary_statement_attendance_ids);
                    });
            })
            ->select([
                DB::raw("salary_statement_attendance_details.id AS `model_id`"),
                DB::raw("'salary_statement_attendance_detail' AS `model_alias`"),
                "salary_statement_attendance_details.salary_statement_attendance_id",

                "salary_statement_attendance_details.date",
                "salary_statement_attendance_details.split_type",
                "salary_statement_attendance_details.split_start",
                "salary_statement_attendance_details.split_end",
                "salary_statement_attendance_details.split_duration",

                "salary_statement_attendance_details.order",
                "salary_statement_attendance_details.work_hour_type",
                "salary_statement_attendance_details.hourly_rate_type",
                DB::raw("0 AS `actual_present`"),

                "salary_statement_attendance_details.hourly_rate",
                "salary_statement_attendance_details.regular_pay",
                "salary_statement_attendance_details.allowance",
                "salary_statement_attendance_details.night_differential_pay",
                "salary_statement_attendance_details.rest_day_pay",
                "salary_statement_attendance_details.leave_pay",

                "salary_statement_attendance_details.holiday_pay",
                "salary_statement_attendance_details.holiday_pay_forfeited",
            ]);

        $regularAttendanceQueryBuilder = $this->queryAsSub($salaryStatementAttendanceQueryBuilder, 'salary_statement_attendance_sub')
            ->joinSub($attendanceDetailQueryBuilder, 'attendance_detail_sub', function ($join) use ($filters) {
                $join->on('attendance_detail_sub.attendance_id', '=', 'salary_statement_attendance_sub.attendance_id')
                    ->when(!empty($filters->salary_statement_attendance_ids) && is_array($filters->salary_statement_attendance_ids), function ($builder) use ($filters) {
                        $builder->whereIn(DB::raw("salary_statement_attendance_sub.id"), $filters->salary_statement_attendance_ids);
                    });
            })
            ->select([
                DB::raw("attendance_detail_sub.id AS `model_id`"),
                DB::raw("'attendance_detail' AS `model_alias`"),
                DB::raw("salary_statement_attendance_sub.id AS `salary_statement_attendance_id`"),

                "attendance_detail_sub.date",
                "attendance_detail_sub.split_type",
                "attendance_detail_sub.split_start",
                "attendance_detail_sub.split_end",
                "attendance_detail_sub.split_duration",

                "attendance_detail_sub.order",
                "attendance_detail_sub.work_hour_type",
                "attendance_detail_sub.hourly_rate_type",
                "attendance_detail_sub.actual_present",

                "attendance_detail_sub.hourly_rate",
                "attendance_detail_sub.regular_pay",
                "attendance_detail_sub.allowance",
                "attendance_detail_sub.night_differential_pay",
                "attendance_detail_sub.rest_day_pay",
                DB::raw("0.000000 AS `leave_pay`"),

                "attendance_detail_sub.holiday_pay",
                DB::raw("null AS `holiday_pay_forfeited`"),
            ]);

        $queryBuilder = $regularAttendanceQueryBuilder->unionAll($payableNonAttendanceQueryBuilder);

        return $this->queryAsSub($queryBuilder, 'salary_statement_attendance_detail_sub')
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'salary_statement_attendance_detail_sub.*'
            ]);
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'salary_statement_attendance_detail_sub.salary_statement_attendance_id', 'direction' => 'ASC'],
            ['field' => 'salary_statement_attendance_detail_sub.order', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
