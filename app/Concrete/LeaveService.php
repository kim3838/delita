<?php

namespace App\Concrete;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\LeaveBalanceAdjustmentType;
use App\Enums\LeaveCarryOverType;
use App\Enums\LeaveIntervalSpanType;
use App\Enums\LeavePeriodType;
use App\Facades\Fractal;
use App\Models\EmployeeLeaveType;
use App\Models\LeaveTypeBalancePerPeriod;
use App\Transformers\LeaveTypeBalancePerPeriod\ListTransformer as LeaveTypeBalancePerPeriodListTransformer;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public bool $carryOverBalancePerNewPeriod = false;
    public ?int $carryOverBalanceLimitValue = null;
    public ?int $carryOverBalanceType = null;
    public int $initialBalanceUponEligibility = 0;
    public Collection $additionalBalancePerPeriod;

    public function getBalanceMap(EmployeeLeaveType $employeeLeaveType, $upToDate): void
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
            WITH RECURSIVE employment_profile_series(employee_id,date_start,date_series) AS (
                SELECT
                    employment_profile_series_sub.employee_id AS `employee_id`,
                    employment_profile_series_sub.employment_profile_start AS `date_start`,
                    employment_profile_series_sub.employment_profile_start AS `date_series`
                FROM (
                    SELECT
                        employment_profiles.employee_id,
                        ROW_NUMBER() OVER(PARTITION BY employee_id ORDER BY start_date, created_at) AS `employment_profile_series`,
                        start_date AS employment_profile_start
                    FROM employment_profiles
                        WHERE employee_id = ?
                        AND status = ?
                        AND start_date < ?
                        ORDER BY created_at
                ) AS employment_profile_series_sub WHERE employment_profile_series_sub.employment_profile_series = 1
                UNION ALL
                SELECT employee_id,date_start, date_series + INTERVAL 1 DAY
                FROM employment_profile_series
                WHERE date_series < ?
            )
            SELECT employee_id,date_start, date_series
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
            ->selectRaw("CONCAT(YEAR(employment_profile_series_sub.date_series), '-', LPAD(MONTH(employment_profile_series_sub.date_series),2,'0')) AS 'year_month'")
            ->selectRaw("$employmentProfileSeriesAlias.employee_id")
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
                DB::raw("employment_profile_by_date_series_sub.year_month"),
                DB::raw("employment_profile_by_date_series_sub.employee_id AS `employee_id`"),
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
                DB::raw("leave_types.carry_over_balance_per_new_period"),
                DB::raw("leave_types.carry_over_balance_type"),
                DB::raw("leave_types.carry_over_balance_value"),
                DB::raw("employee_leave_type.leave_type_id"),
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
                DB::raw("COALESCE(JSON_CONTAINS(leave_types.eligibility_employment_types, CAST(epbds.employment_type AS CHAR)),0) AS `eligible`"),
                DB::raw("CASE WHEN employee_leave_type.override_balance_upon_eligibility = 1 THEN employee_leave_type.balance_upon_eligibility ELSE leave_types.initial_balance_upon_eligibility END AS `balance_upon_eligibility`")
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
                                    1+SUM(
                                        IF(
                                            CONCAT(YEAR(eltsbp.date_series),LPAD(MONTH(eltsbp.date_series), 2, '0'),LPAD(DAY(eltsbp.date_series), 2, '0')) = CONCAT(eltsbp.year, LPAD(eltsbp.period_span_value, 2, '0'), '01')
                                            AND eltsbp.eligibility_started > 0,
                                        1, 0)
                                    ) OVER(ORDER BY eltsbp.year,eltsbp.month)
                        END
                    END AS `period`
                "),
            ]);

        $employeeLeaveTypeBalancePipelineQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employeeLeaveTypeByPeriodQueryBuilder, 'eltbp')
            ->select([
                DB::raw("eltbp.*"),
                DB::raw("
                    (
                        SELECT SUM(balance) FROM leave_balance_adjustments lba
                        WHERE type = " . LeaveBalanceAdjustmentType::ADD->value . " AND employee_id = eltbp.employee_id AND leave_type_id = eltbp.leave_type_id AND effective_date = eltbp.date_series
                        GROUP BY lba.employee_id, lba.leave_type_id, lba.type, lba.effective_date
                    ) AS `running_balance_additions`
                "),
                DB::raw("
                    COALESCE((
                        SELECT SUM(balance) FROM leave_balance_adjustments lba
                        WHERE type = " . LeaveBalanceAdjustmentType::DEDUCT->value . " AND employee_id = eltbp.employee_id AND leave_type_id = eltbp.leave_type_id AND effective_date = eltbp.date_series
                        GROUP BY lba.employee_id, lba.leave_type_id, lba.type, lba.effective_date
                    ),0) AS `running_balance_deductions`
                "),
                DB::raw("
                    (
                        SELECT COUNT(*) FROM leaves
                        WHERE employee_id = eltbp.employee_id AND leave_type_id = eltbp.leave_type_id AND date = eltbp.date_series
                    ) AS `claims`
                "),
            ]);

        $employeeLeaveTypeBalancePipelineTotalsByPeriodQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employeeLeaveTypeBalancePipelineQueryBuilder, 'eltbpl')
            ->select([
                DB::raw("eltbpl.*"),
                DB::raw("(SUM(eltbpl.claims) OVER(PARTITION BY eltbpl.period) + SUM(eltbpl.running_balance_deductions) OVER(PARTITION BY eltbpl.period)) AS `period_claims_and_deductions`"),
            ]);

        _log_query_builder_with_bindings($employeeLeaveTypeBalancePipelineTotalsByPeriodQueryBuilder);

        $periodByDateSeriesCollection = $employeeLeaveTypeBalancePipelineTotalsByPeriodQueryBuilder->get()->toArray();

        _clear_debug();

        $this->initialBalanceUponEligibility = $periodByDateSeriesCollection[0]->balance_upon_eligibility;
        $this->carryOverBalancePerNewPeriod = boolval($periodByDateSeriesCollection[0]->carry_over_balance_per_new_period);

        if($this->carryOverBalancePerNewPeriod){
            $this->carryOverBalanceType = $periodByDateSeriesCollection[0]->carry_over_balance_type;

            if($this->carryOverBalanceType == LeaveCarryOverType::LIMIT->value){
                $this->carryOverBalanceLimitValue = (int)$periodByDateSeriesCollection[0]->carry_over_balance_value;

                $this->carryOverBalancePerNewPeriod = $this->carryOverBalanceLimitValue > 0;
            }
        }

        $additionalBalancePerPeriodCollection = Fractal::collection(
            LeaveTypeBalancePerPeriod::query()->where('leave_type_id', $periodByDateSeriesCollection[0]->leave_type_id)->get(),
            LeaveTypeBalancePerPeriodListTransformer::class
        )['data'];

        $this->additionalBalancePerPeriod = collect($additionalBalancePerPeriodCollection);

        $startingPeriod = 1;
        $this->setBalancePerPeriod(
            $periodByDateSeriesCollection,
            $startingPeriod,
            $this->initialBalanceUponEligibility + $this->getPeriodAdditionalBalance($startingPeriod)
        );

        $claimsAndDeductionsByPeriodQueryBuilder = app(QueryBuilder::class)
            ->fromSub($employeeLeaveTypeBalancePipelineTotalsByPeriodQueryBuilder, 'eltbptbp')
            ->select([
                DB::raw("eltbptbp.period"),
                DB::raw("eltbptbp.period_claims_and_deductions"),
            ])
            ->whereNotNull(DB::raw("eltbptbp.period"))
            ->groupBy([
                DB::raw("eltbptbp.period"),
                DB::raw("eltbptbp.period_claims_and_deductions"),
            ])
            ->orderBy(DB::raw("eltbptbp.period"));

        $claimsAndDeductionsByPeriodCollection = $claimsAndDeductionsByPeriodQueryBuilder->get()->toArray();

        /**
         * If carry over enabled,
         * deduct balance on all period before the period and current period,
         * then recompute carry over on periods after
         **/
        if($this->carryOverBalancePerNewPeriod){

            foreach ($claimsAndDeductionsByPeriodCollection as $claimsAndDeductionsByPeriod) {

                $period = (int)$claimsAndDeductionsByPeriod->period;
                $balance = (int)$claimsAndDeductionsByPeriod->period_claims_and_deductions;

                if($balance <= 0){
                    continue;
                }

                list(
                    $reComputePeriodStart,
                    $reComputeRunningBalance
                ) = $this->deductRunningBalance($periodByDateSeriesCollection, $period, $balance);

                $this->setBalancePerPeriod(
                    $periodByDateSeriesCollection,
                    $reComputePeriodStart,
                    $reComputeRunningBalance
                );
            }
        }

        /**
         * If carry over not enabled, deduct balance only on its period
         **/
        if(!$this->carryOverBalancePerNewPeriod){

            foreach ($claimsAndDeductionsByPeriodCollection as $claimsAndDeductionsByPeriod) {

                $period = (int)$claimsAndDeductionsByPeriod->period;
                $balance = (int)$claimsAndDeductionsByPeriod->period_claims;

                if($balance <= 0){
                    continue;
                }

                $this->deductBalanceOnPeriod($periodByDateSeriesCollection, $period, $balance);
            }
        }

        /**
         * Group date series by date and running balance spanning from the beginning and end of the month
         **/
        $groupedByYearMonthPeriod = $this->groupByYearMonthPeriod($this->mapToYearMonthEmploymentTypeDecodedAsKey($periodByDateSeriesCollection));

        $groupedByYearMonthPeriod = $this->decodeYearMonthPeriodKeys($groupedByYearMonthPeriod);

        $singleLinePerBalance = $this->transformEachToSingleLine($periodByDateSeriesCollection);

        _debug([
            'monthly_balance' => $groupedByYearMonthPeriod->values()->all(),
            'claims_by_period' => $claimsAndDeductionsByPeriodCollection,
        ]);
    }

    public function decodeYearMonthPeriodKeys($groupedByYearMonthEmploymentType)
    {
        return $groupedByYearMonthEmploymentType
            ->map(function($yearMonthCollection, $yearMonthKey){

                $yearMonth = json_decode($yearMonthKey, true);

                $mappedYearMonthCollection = $yearMonthCollection->map(function($yearMonthItem, $periodKey){

                    $mappedYearMonthItem = $yearMonthItem->groupBy('employment')->map(function($periodCollection, $employmentKey){

                        $employment = json_decode($employmentKey, true);

                        $mappedDateSeries = [];
                        $previousRunningBalance = null;
                        $firstPeriodCollection = $periodCollection->first();
                        $lastPeriodCollection = $periodCollection->last();
                        $firstPeriodDay = Carbon::parse($firstPeriodCollection['date_series'])->day;
                        $lastPeriodDay = Carbon::parse($lastPeriodCollection['date_series'])->day;

                        foreach ($periodCollection->toArray() as $periodItem){
                            $dateSeries = Carbon::parse($periodItem['date_series']);

                            if($previousRunningBalance !== $periodItem['running_balance'] || $dateSeries->day == $lastPeriodDay){

                                if($dateSeries->day > (($firstPeriodDay + 1)) && !isset($mappedDateSeries[$dateSeries->day - 1]) && $dateSeries->day !== $lastPeriodDay){
                                    $mappedDateSeries[$dateSeries->day - 1] = $previousRunningBalance;
                                }

                                $mappedDateSeries[$dateSeries->day] = $periodItem['running_balance'];
                            }

                            $previousRunningBalance = $periodItem['running_balance'];
                        }

                        return [
                            'type'  => EmploymentType::tryFrom($employment['type'])?->toArray(),
                            'eligible' => boolval($employment['eligible']),
                            'value' => $mappedDateSeries
                        ];
                    });

                    return [
                        'period' => $periodKey,
                        'value' => $mappedYearMonthItem->values()->all()
                    ];
                });

                return [
                    'year'  => $yearMonth['year'],
                    'month' => $yearMonth['month'],
                    'month_readable' => Carbon::createFromFormat('m', $yearMonth['month'])->format('F'),
                    'value' => $mappedYearMonthCollection->values()->all()
                ];
            });
    }

    public function groupByYearMonthPeriod($periodByDateSeriesCollection)
    {
        return $periodByDateSeriesCollection->groupBy(['year_month', function ($item) {
            return $item['period'];
        }]);
    }

    public function mapToYearMonthEmploymentTypeDecodedAsKey($periodByDateSeriesCollection): Collection
    {
        return collect($periodByDateSeriesCollection)->map(function($item){
            return [
                'year_month' => json_encode([
                    'year' => $item->year,
                    'month' => $item->month,
                ]),
                'date_series' => $item->date_series,
                'employment' => json_encode([
                    'type' => $item->employment_type,
                    'eligible' => $item->eligible,
                ]),
                'period' => ($item->period ?? '0'),
                'running_balance' => $item->running_balance ?? 0,
            ];
        });
    }

    public function mapToBasicInfo($periodByDateSeriesCollection): Collection
    {
        return collect($periodByDateSeriesCollection)->map(function($item){
            return [
                'year_month' => $item->year_month,
                'date_series' => $item->date_series,
                'employment_type' => $item->employment_type,
                'eligible' => $item->eligible,
                'period' => ($item->period ?? '0'),
                'running_balance' => $item->running_balance ?? 0,
            ];
        });
    }

    public function transformEachToSingleLine($periodByDateSeriesCollection): array
    {
        return collect($periodByDateSeriesCollection)->map(function($item){
            return $item->date_series . ';' .
                ($item->period ? 'PR' . str_pad($item->period, 3, '0', STR_PAD_LEFT) : '_____') . ';' .
                ((isset($item->running_balance) && is_numeric($item->running_balance)) ? $item->running_balance : '___');
        })->values()->toArray();
    }

    public function setBalancePerPeriod($periodByDateSeriesCollection, $startingPeriod, $runningBalance): void
    {
        $period = $startingPeriod;
        //Once per period: balance from leave type balance per period
        $periodAdditionalBalance = null;
        //Treat custom starting period(period that is > 1) as new period once, flag to false when claimed
        $customStartingPeriodAsNewPeriod = true;

        //Debug carry over and additional balance per period
        if(false){
            _debug([
                'carry_over_settings' => [
                    '$carryOverBalancePerNewPeriod' => $this->carryOverBalancePerNewPeriod,
                    '$carryOverBalanceType' => LeaveCarryOverType::tryFrom($this->carryOverBalanceType)?->label(),
                    '$carryOverBalanceLimitValue' => $this->carryOverBalanceLimitValue,
                ],
                'additional_balance_per_period' => $this->additionalBalancePerPeriod->map(function($item){
                    return [
                        'from_period' => $item['from_period'],
                        'and_so_on' => $item['and_so_on'],
                        'to_period' => $item['to_period'],
                        'balance' => $item['balance'],
                    ];
                })->toArray(),
            ]);
        }

        foreach ($periodByDateSeriesCollection as $periodByDateSeries) {

            if(empty($periodByDateSeries->period) || intval($periodByDateSeries->period) < $startingPeriod){
                continue;
            }

            $newPeriod = false;

            //Once: When period is greater than 1 and is equal to custom starting period, treat it as new period
            if($customStartingPeriodAsNewPeriod && ($period > 1 && $period == $startingPeriod)){
                $newPeriod = true;
                $customStartingPeriodAsNewPeriod = false;
            }

            if(intval($periodByDateSeries->period) > $period){
                $period += 1;
                $periodAdditionalBalance = null;
                $newPeriod = true;
            }

            if($newPeriod){

                if($periodAdditionalBalance == null && !is_numeric($periodAdditionalBalance)){
                    //Get additional balance per period
                    $periodAdditionalBalance = $this->getPeriodAdditionalBalance($periodByDateSeries->period);
                }

                //Once per period: Check for carry over, if not enabled, running balance resets to 0
                if($this->carryOverBalancePerNewPeriod){
                    if($this->carryOverBalanceType == LeaveCarryOverType::LIMIT->value){
                        $runningBalance = $runningBalance >= $this->carryOverBalanceLimitValue
                            ? $this->carryOverBalanceLimitValue
                            : $runningBalance;
                    }
                } else {
                    $runningBalance = 0;
                }

                //Once per period: Add additional balance per period
                $runningBalance += $periodAdditionalBalance;
            }

            if(is_numeric($periodByDateSeries->running_balance_additions)){
                $runningBalance += (int)$periodByDateSeries->running_balance_additions;
            }

            //Set date series running balance
            $periodByDateSeries->running_balance = $runningBalance;
        }
    }

    public function getPeriodAdditionalBalance($period)
    {
        $periodAdditionalBalances = $this->additionalBalancePerPeriod->filter(function($item) use($period){
            return ($item['from_period'] <= $period && !$item['and_so_on'] && $item['to_period'] >= $period)
                || ($item['from_period'] <= $period && $item['and_so_on']);
        });

        $mappedPeriodAdditionalBalances = $periodAdditionalBalances->map(function($item){
            return [
                'from_period' => $item['from_period'],
                'and_so_on' => $item['and_so_on'],
                'to_period' => $item['to_period'],
                'balance' => $item['balance'],
            ];
        })->toArray();

        return $periodAdditionalBalances->sum('balance');
    }

    public function deductRunningBalance(&$periodByDateSeriesCollection, $periodOrigin, $balance): array
    {
        $period = 1;
        $reComputeCarryOverFlag = false;
        $reComputeRunningBalance = 0;

        foreach ($periodByDateSeriesCollection as $periodByDateSeries) {

            if(empty($periodByDateSeries->period)){
                continue;
            }

            $newPeriod = false;

            if(intval($periodByDateSeries->period) > $period){
                $period += 1;
                $newPeriod = true;
            }

            if($newPeriod){

                if(!$reComputeCarryOverFlag && $period > $periodOrigin){
                    break;
                }
            }

            //Perform deduction on periods lesser and equal to period origin
            if(!$reComputeCarryOverFlag && $period <= $periodOrigin){
                //Deduct balance
                $periodByDateSeries->running_balance -= $balance;

                //Set running balance re-computer
                $reComputeRunningBalance = $periodByDateSeries->running_balance;
            }
        }

        return [
            $period,
            $reComputeRunningBalance
        ];
    }

    public function deductBalanceOnPeriod(&$periodByDateSeriesCollection, $periodOrigin, $balance): void
    {
        foreach ($periodByDateSeriesCollection as $periodByDateSeries) {

            if(empty($periodByDateSeries->period) || intval($periodByDateSeries->period) !== $periodOrigin){
                continue;
            }

            //Deduct balance
            $periodByDateSeries->running_balance -= $balance;
        }
    }
}
