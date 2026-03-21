<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\OvertimeRequestRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\RequestApprovalStatus;
use App\Models\OvertimeRequest;
use App\Models\RequestApprovalState;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class OvertimeRequestRepositoryEloquent extends BaseRepositoryEloquent implements OvertimeRequestRepository
{
    public function model(): string
    {
        return OvertimeRequest::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $attendanceRepositoryFilter = clone $filters;

        unset($attendanceRepositoryFilter->search);
        if(isset($attendanceRepositoryFilter->attendance_search)){
            $attendanceRepositoryFilter->search = $attendanceRepositoryFilter->attendance_search;
        }
        if(isset($attendanceRepositoryFilter->attendance_date_from)){
            $attendanceRepositoryFilter->date_from = $attendanceRepositoryFilter->attendance_date_from;
        }
        if(isset($attendanceRepositoryFilter->attendance_date_to)){
            $attendanceRepositoryFilter->date_to = $attendanceRepositoryFilter->attendance_date_to;
        }

        unset($filters->attendance_search);
        unset($filters->attendance_date_from);
        unset($filters->attendance_date_to);

        $requestedByCompanyUserRepositoryFilter = clone $filters;
        if(isset($this->requestInterface->accountId)){
            $requestedByCompanyUserRepositoryFilter->account_id = $this->requestInterface->accountId;
        }
        if(isset($filters->company_id)){
            $requestedByCompanyUserRepositoryFilter->associated_companies = [$filters->company_id];
        }
        unset($requestedByCompanyUserRepositoryFilter->company_id);
        unset($requestedByCompanyUserRepositoryFilter->user_ids);
        unset($requestedByCompanyUserRepositoryFilter->search);

        $attendanceQueryBuilder = App::make(AttendanceRepository::class)->baseQueryBuilder($attendanceRepositoryFilter, []);

        $requestedByCompanyUserQueryBuilder = App::make(CompanyUserRepository::class)->baseQueryBuilder($requestedByCompanyUserRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($attendanceQueryBuilder, 'attendance_sub', function ($join) {
                $join->on('attendance_sub.id', '=', 'overtime_requests.attendance_id');
            })
            ->joinSub($this->statusQueryBuilder(), 'status_sub', function ($join) {
                $join->on('status_sub.id', '=', 'overtime_requests.id');
            })
            ->leftJoinSub($requestedByCompanyUserQueryBuilder, 'requested_by_company_user_sub', function ($join) {
                $join->on('requested_by_company_user_sub.user_id', '=', 'overtime_requests.requested_by');
            })
            ->join('companies', 'attendance_sub.employee_company_id', '=', 'companies.id')
            ->when(!empty($filters->requested_by_ids) && is_array($filters->requested_by_ids), function ($builder) use ($filters) {
                $builder->whereIn('overtime_requests.requested_by', $filters->requested_by_ids);
            })
            ->when(!empty($filters->statuses) && is_array($filters->statuses), function ($builder) use ($filters) {
                $builder->whereIn('status_sub.status_summary', $filters->statuses);
            })
            ->when(!empty($filters->request_numbers) && is_array($filters->request_numbers), function ($builder) use ($filters) {
                $builder->whereIn('overtime_requests.number', $filters->request_numbers);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('overtime_requests.number', 'LIKE', "%$value%");
                });
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
                 * Overtime Request
                 **/
                'overtime_requests.id AS id',
                'overtime_requests.company_id AS company_id',
                'overtime_requests.number AS number',
                'overtime_requests.requested_by AS requested_by',
                DB::raw("CONVERT_TZ(overtime_requests.date_requested, 'UTC', companies.timezone) AS date_requested"),
                'companies.timezone AS company_timezone',
                'overtime_requests.attendance_id AS attendance_id',
                'overtime_requests.start AS start',
                'overtime_requests.end AS end',
                'overtime_requests.duration AS duration',
                'overtime_requests.remarks AS remarks',
                'status_sub.status_summary AS status_summary',

                /**
                 * Requested by
                 **/
                'requested_by_company_user_sub.company_timezone AS requested_by_user_company_timezone',
                'requested_by_company_user_sub.user_id AS requested_by_user_id',
                'requested_by_company_user_sub.user_username AS requested_by_user_username',
                'requested_by_company_user_sub.is_employee AS requested_by_user_is_employee',
                'requested_by_company_user_sub.company_employee_number AS requested_by_user_company_employee_number',
                'requested_by_company_user_sub.company_employee_full_name AS requested_by_user_company_employee_full_name',
            ]);

        return $queryBuilder;
    }

    public function statusQueryBuilder(): QueryBuilder
    {
        $declined = RequestApprovalStatus::DECLINED->value;
        $approved = RequestApprovalStatus::APPROVED->value;
        $pending = RequestApprovalStatus::PENDING->value;

        $queryBuilder = RequestApprovalState::query()->getQuery()
            ->where('requestable_type', Relation::getMorphAlias($this->model()))
            ->select([
                DB::raw("request_approval_states.requestable_id"),
                DB::raw("COUNT(*) OVER(PARTITION BY request_approval_states.requestable_id) AS total_approvers"),
                DB::raw("MAX(request_approval_states.status = " . $declined . ") OVER(PARTITION BY request_approval_states.requestable_id) AS at_least_one_declined"),
                DB::raw("MIN(request_approval_states.status = " . $approved . ") OVER(PARTITION BY request_approval_states.requestable_id) = 1 AS all_approved"),
                DB::raw("SUM(request_approval_states.status <> " . $pending . ") OVER(PARTITION BY request_approval_states.requestable_id) = 0 AS all_pending"),
                DB::raw("MAX(request_approval_states.status = " . $pending . ") OVER(PARTITION BY request_approval_states.requestable_id) AS at_least_one_pending"),
            ]);

        $queryBuilder = $this->model::query()->getQuery()
            ->leftJoinSub($queryBuilder, 'status_sub', function ($join) {
                $join->on('status_sub.requestable_id', '=', 'overtime_requests.id');
            })
            ->select([
                'overtime_requests.id',
                DB::raw("
                    CASE WHEN SUM(status_sub.at_least_one_declined) > 0 THEN " . $declined . " ELSE (
                        CASE WHEN SUM(status_sub.all_approved) = status_sub.total_approvers THEN " . $approved . " ELSE (
                            CASE WHEN SUM(status_sub.all_pending) = status_sub.total_approvers THEN " . $pending . " ELSE (
                                CASE WHEN SUM(status_sub.at_least_one_pending) > 0 THEN " . $pending . " ELSE " . $pending . " END
                            ) END
                        ) END
                    )
                    END AS status_summary
                "),
            ]);

        $this->setGroupsOnBuilder($queryBuilder, ['overtime_requests.id']);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'overtime_requests.date_requested', 'direction' => 'DESC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters): Collection
    {
        $queryBuilder = $this->baseQueryBuilder($filters);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public function store($attributes)
    {
        return $this->model::query()->create([
            ...$attributes,
            'duration' => abs(Carbon::parse($attributes['end'])->diffInMinutes(Carbon::parse($attributes['start'])))
        ]);
    }

    public function showFromFilters($filters)
    {
        return $this->list($filters)->first();
    }
}
