<?php

namespace App\Concrete;

use App\Enums\EmploymentStatus;
use App\Enums\LeaveIntervalSpanType;
use App\Enums\LeavePeriodType;
use App\Models\EmployeeLeaveType;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function balanceByPeriod(EmployeeLeaveType $employeeLeaveType, $upToDate): void
    {
        $employee = clone $employeeLeaveType->employee;
        $leaveType = clone $employeeLeaveType->leaveType;
        $dateSeriesPartition = false;
        $dateSeriesPartitionColumn = DB::raw("
            DENSE_RANK() OVER(
                PARTITION BY employment_profile_by_date_series_sub.year
                ORDER BY
                    employment_profile_by_date_series_sub.year,
                    employment_profile_by_date_series_sub.month,
                    employment_profile_by_date_series_sub.employment_type
            ) as `date_series_partition`
        ");

        $employmentProfileSeries = "
            WITH RECURSIVE employment_profile_series(date_start,date_series) AS (
                SELECT
                    employment_profile_series_sub.employment_profile_start AS `date_start`,
                    employment_profile_series_sub.employment_profile_start AS `date_series`
                FROM (
                    SELECT
                        ROW_NUMBER() OVER(PARTITION BY employee_id ORDER BY start_date, created_at) AS `employment_profile_series`,
                        start_date AS employment_profile_start
                    FROM employment_profiles
                        WHERE employee_id = ?
                        AND status = ?
                        AND start_date < ?
                        ORDER BY created_at
                ) AS employment_profile_series_sub WHERE employment_profile_series_sub.employment_profile_series = 1
                UNION ALL
                SELECT date_start, date_series + INTERVAL 1 DAY
                FROM employment_profile_series
                WHERE date_series < ?
            )
            SELECT date_start, date_series
            FROM employment_profile_series
        ";

        $employmentProfileSeriesParams = [
            'parameter_employee_id' => $employee->id,
            'parameter_employment_status' => EmploymentStatus::ACTIVE->value,
            'parameter_date_to' => $upToDate
        ];

        $employmentProfileSeriesQueryBuilder = app(QueryBuilder::class)
            ->fromRaw(
                "($employmentProfileSeries) as employment_profile_series",
                [
                    $employmentProfileSeriesParams['parameter_employee_id'],
                    $employmentProfileSeriesParams['parameter_employment_status'],
                    $employmentProfileSeriesParams['parameter_date_to'],
                    $employmentProfileSeriesParams['parameter_date_to'],
                ]
            );

        $employmentProfileSeriesAlias = 'employment_profile_series_sub';

        $employmentProfileByDateSeries = "
            SELECT
                current_employment_profile.employment_type
            FROM (
            SELECT
                ROW_NUMBER() OVER(PARTITION BY employee_id ORDER BY start_date DESC, created_at DESC) AS `row_number`,
                employment_profile_sub_query.*
                FROM (
                    SELECT
                        $employmentProfileSeriesAlias.date_series AS local_date,
                        employment_profiles.*
                    FROM employment_profiles
                    LEFT JOIN employees on employees.id = employment_profiles.employee_id
                    WHERE `status` = ? AND employee_id = ?
                ) AS employment_profile_sub_query
                WHERE start_date <= employment_profile_sub_query.local_date
                AND (end_date IS NULL OR end_date >= employment_profile_sub_query.local_date)
            ) AS current_employment_profile
            WHERE current_employment_profile.row_number = 1
        ";

        $employmentProfileByDateSeriesSubQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employmentProfileSeriesQueryBuilder, $employmentProfileSeriesAlias)
            ->selectRaw("YEAR(employment_profile_series_sub.date_series) AS year")
            ->selectRaw("MONTH(employment_profile_series_sub.date_series) AS month")
            ->selectRaw("$employmentProfileSeriesAlias.date_start")
            ->selectRaw("$employmentProfileSeriesAlias.date_series")
            ->selectRaw("($employmentProfileByDateSeries) as employment_type", [
                $employmentProfileSeriesParams['parameter_employment_status'],
                $employmentProfileSeriesParams['parameter_employee_id']
            ]);

        $employmentProfileByDateSeriesQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employmentProfileByDateSeriesSubQueryBuilder, 'employment_profile_by_date_series_sub')
            ->select([
                DB::raw("employment_profile_by_date_series_sub.year"),
                DB::raw("employment_profile_by_date_series_sub.month"),
                DB::raw("employment_profile_by_date_series_sub.date_start"),
                DB::raw("employment_profile_by_date_series_sub.date_series"),
                //Date series partition slot
                DB::raw("employment_profile_by_date_series_sub.employment_type"),
            ]);

        $employeeLeaveTypeEligibilityQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employmentProfileByDateSeriesQueryBuilder, 'epbds')
            ->join('employee_leave_type', function ($join) use($employeeLeaveType) {
                $join->where('employee_leave_type.leave_type_id', $employeeLeaveType->leave_type_id)
                    ->where('employee_leave_type.employee_id', $employeeLeaveType->employee_id);
            })
            ->leftJoin('leave_types', 'leave_types.id', '=', 'employee_leave_type.leave_type_id')
            ->select([
                DB::raw("epbds.*"),
                DB::raw("leave_types.period_type"),
                DB::raw("leave_types.period_interval_span_type"),
                DB::raw("
                    CASE
                        WHEN leave_types.period_type = ".LeavePeriodType::INTERVAL->value." AND (leave_types.period_interval_span_type = ".LeaveIntervalSpanType::DAY->value." OR leave_types.period_interval_span_type = ".LeaveIntervalSpanType::MONTH->value.")
                            THEN leave_types.period_interval_span_value
                        WHEN leave_types.period_type = ".LeavePeriodType::INTERVAL->value." AND leave_types.period_interval_span_type = ".LeaveIntervalSpanType::YEAR->value."
                            THEN (leave_types.period_interval_span_value * 12)
                        WHEN leave_types.period_type = ".LeavePeriodType::CALENDAR_YEAR->value."
                            THEN leave_types.period_calendar_span_value
                    END AS `period_span_value`
                "),
                DB::raw("COALESCE(JSON_CONTAINS(leave_types.eligibility_employment_types, CAST(epbds.employment_type AS CHAR)),0) AS `eligible`")
            ]);

        $employeeLeaveTypeSequenceStartQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employeeLeaveTypeEligibilityQueryBuilder, 'elte')
            ->select([
                DB::raw("elte.*"),
                DB::raw("SUM(eligible) OVER(ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) > 0 AS `eligibility_started`"),
            ]);

        $employeeLeaveTypeEligibilityDateStartReferenceQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employeeLeaveTypeSequenceStartQueryBuilder, 'eltss')
            ->select([
                DB::raw("eltss.*"),
                DB::raw("
                    CASE
                        WHEN eltss.eligibility_started = 1 AND LAG(eltss.eligibility_started) OVER(ORDER BY eltss.date_series) IS NULL
                        THEN eltss.date_start
                        WHEN eltss.eligibility_started > 0 AND LAG(eltss.eligibility_started) OVER(ORDER BY eltss.date_series) = 0
                        THEN eltss.date_series
                    END AS `eligibility_date_start_reference`
                "),
            ]);

        $employeeLeaveTypeEligibilityDateStartQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employeeLeaveTypeEligibilityDateStartReferenceQueryBuilder, 'eltsr')
            ->select([
                DB::raw("eltsr.*"),
                DB::raw("
                    CASE
                        WHEN eltsr.eligibility_started > 0
                        THEN MAX(eltsr.eligibility_date_start_reference) OVER(ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW)
                    END AS `eligibility_date_start`
                "),
            ]);

        $employeeLeaveTypeSequenceByPeriodQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employeeLeaveTypeEligibilityDateStartQueryBuilder, 'elteds')
            ->select([
                DB::raw("elteds.*"),
                DB::raw("
                    CASE
                        WHEN
                            (elteds.period_type = ".LeavePeriodType::INTERVAL->value." AND (elteds.period_interval_span_type = ".LeaveIntervalSpanType::MONTH->value." OR elteds.period_interval_span_type = ".LeaveIntervalSpanType::YEAR->value."))
                            OR elteds.period_type = ".LeavePeriodType::CALENDAR_YEAR->value."
                        THEN 1+PERIOD_DIFF(
                                CONCAT(YEAR(elteds.date_series),LPAD(MONTH(elteds.date_series), 2, '0')),
                                CONCAT(YEAR(elteds.eligibility_date_start),LPAD(MONTH(elteds.eligibility_date_start), 2, '0'))
                            )
                        WHEN elteds.period_type = ".LeavePeriodType::INTERVAL->value." AND elteds.period_interval_span_type = ".LeaveIntervalSpanType::DAY->value."
                        THEN 1+DATEDIFF(elteds.date_series,elteds.eligibility_date_start)
                    END AS `sequence_by_period_type`
                "),
            ]);

        $employeeLeaveTypeByPeriodQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employeeLeaveTypeSequenceByPeriodQueryBuilder, 'eltsbp')
            ->select([
                DB::raw("eltsbp.*"),
                DB::raw("
                    CASE WHEN eltsbp.eligibility_started > 0 THEN
                        CASE
                            WHEN eltsbp.period_type = ".LeavePeriodType::INTERVAL->value."
                                THEN
                                CEIL(eltsbp.sequence_by_period_type / eltsbp.period_span_value)
                            WHEN eltsbp.period_type = ".LeavePeriodType::CALENDAR_YEAR->value."
                                THEN
                                    (IF(MONTH(eltsbp.eligibility_date_start) < eltsbp.period_span_value, 1, 0))
                                    +SUM(IF(CONCAT(YEAR(eltsbp.date_series),LPAD(MONTH(eltsbp.date_series), 2, '0'),LPAD(DAY(eltsbp.date_series), 2, '0')) = CONCAT(eltsbp.year, LPAD(eltsbp.period_span_value, 2, '0'), '01'), 1, 0)
                                ) OVER(ORDER BY eltsbp.year,eltsbp.month)
                        END
                    END AS `period`
                "),
            ]);
    }
}
