<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\LeaveRequestRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\RequestApprovalStatus;
use App\Models\LeaveRequest;
use App\Models\RequestApprovalState;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class LeaveRequestRepositoryEloquent extends BaseRepositoryEloquent implements LeaveRequestRepository
{
    public function model(): string
    {
        return LeaveRequest::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $employeeRepositoryFilter = clone $filters;
        if(isset($employeeRepositoryFilter->employee_search)){
            $employeeRepositoryFilter->search = $employeeRepositoryFilter->employee_search;
        }
        unset($employeeRepositoryFilter->user_search);
        unset($employeeRepositoryFilter->employee_search);
        unset($employeeRepositoryFilter->associated_companies);

        $employeeQueryBuilder = App::make(EmployeeRepository::class)->baseQueryBuilder($employeeRepositoryFilter, []);

        $queryBuilder = $this->model::query()->getQuery()
            ->joinSub($employeeQueryBuilder, 'employee_sub', function ($join) {
                $join->on('employee_sub.id', '=', 'leave_requests.employee_id');
            })
            ->joinSub($this->statusQueryBuilder(), 'status_sub', function ($join) {
                $join->on('status_sub.id', '=', 'leave_requests.id');
            })
            ->join('companies', 'leave_requests.company_id', '=', 'companies.id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->when(!empty($filters->requested_by_ids) && is_array($filters->requested_by_ids), function ($builder) use ($filters) {
                $builder->whereIn('leave_requests.requested_by', $filters->requested_by_ids);
            })
            ->when(!empty($filters->statuses) && is_array($filters->statuses), function ($builder) use ($filters) {
                $builder->whereIn('status_sub.status_summary', $filters->statuses);
            })
            ->when(!empty($filters->request_numbers) && is_array($filters->request_numbers), function ($builder) use ($filters) {
                $builder->whereIn('leave_requests.number', $filters->request_numbers);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('leave_requests.number', 'LIKE', "%$value%");
                });
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),

                /**
                 * Employee
                 **/
                "employee_sub.number AS employee_number",
                "employee_sub.family_name AS employee_family_name",
                "employee_sub.middle_name AS employee_middle_name",
                "employee_sub.given_name AS employee_given_name",

                /**
                 * Leave
                 **/
                "leave_types.code AS leave_type_code",
                "leave_types.name AS leave_type_name",

                /**
                 * Leave Request
                 **/
                'leave_requests.id AS id',
                'leave_requests.company_id AS company_id',
                'leave_requests.number AS number',
                'leave_requests.requested_by AS requested_by',
                DB::raw("CONVERT_TZ(leave_requests.date_requested, 'UTC', companies.timezone) AS date_requested"),
                'companies.timezone AS company_timezone',
                'leave_requests.employee_id AS employee_id',
                'leave_requests.shift_id AS shift_id',
                'leave_requests.leave_type_id AS leave_type_id',
                'leave_requests.date_from AS date_from',
                'leave_requests.date_to AS date_to',
                'leave_requests.remarks AS remarks',
                'status_sub.status_summary AS status_summary',
            ]);

        return $queryBuilder;
    }

    public function statusQueryBuilder(): QueryBuilder
    {
        $declined = RequestApprovalStatus::DECLINED->value;
        $approved = RequestApprovalStatus::APPROVED->value;
        $pending = RequestApprovalStatus::PENDING->value;

        $queryBuilder = RequestApprovalState::query()->getQuery()
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
                $join->on('status_sub.requestable_id', '=', 'leave_requests.id');
            })
            ->select([
                'leave_requests.id',
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

        $this->setGroupsOnBuilder($queryBuilder, ['leave_requests.id']);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'leave_requests.date_requested', 'direction' => 'DESC'],
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

    public function showFromFilters($filters)
    {
        return $this->list($filters)->first();
    }
}
