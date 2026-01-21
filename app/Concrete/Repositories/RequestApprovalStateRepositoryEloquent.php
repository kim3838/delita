<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\RequestApprovalStatus;
use App\Models\RequestApprovalState;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class RequestApprovalStateRepositoryEloquent extends BaseRepositoryEloquent implements RequestApprovalStateRepository
{
    public function model(): string
    {
        return RequestApprovalState::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $companyUserRepositoryFilter = clone $filters;
        if(isset($companyUserRepositoryFilter->user_ids)){
            $companyUserRepositoryFilter->pre_selected_user_ids = $companyUserRepositoryFilter->user_ids;
        }
        unset($companyUserRepositoryFilter->user_ids);
        unset($companyUserRepositoryFilter->search);

        $declined = RequestApprovalStatus::DECLINED->value;
        $approved = RequestApprovalStatus::APPROVED->value;
        $pending = RequestApprovalStatus::PENDING->value;

        $queryBuilder = $this->model::query()->getQuery()
            ->select([
                DB::raw("request_approval_states.id"),
                DB::raw("request_approval_states.requestable_type"),
                DB::raw("request_approval_states.requestable_id"),
                DB::raw("
                    CASE WHEN requestable_type = 'attendance_adjustment_request' THEN
                        (SELECT companies.account_id FROM attendance_adjustment_requests LEFT JOIN companies ON companies.id = attendance_adjustment_requests.company_id WHERE attendance_adjustment_requests.id = requestable_id)
                    ELSE '' END AS requestable_account_id
                "),
                DB::raw("
                    CASE WHEN requestable_type = 'attendance_adjustment_request' THEN
                        (SELECT company_id FROM attendance_adjustment_requests WHERE id = requestable_id)
                    ELSE '' END AS requestable_company_id
                "),
                DB::raw("
                    CASE WHEN requestable_type = 'attendance_adjustment_request' THEN
                        (SELECT number FROM attendance_adjustment_requests WHERE id = requestable_id)
                    ELSE '' END AS requestable_number
                "),
                DB::raw("
                    CASE WHEN requestable_type = 'attendance_adjustment_request' THEN
                        (SELECT date_requested FROM attendance_adjustment_requests WHERE id = requestable_id)
                    ELSE '' END AS requestable_date_requested
                "),
                DB::raw("request_approval_states.order"),
                DB::raw("request_approval_states.approver_id"),
                DB::raw("request_approval_states.remarks"),
                DB::raw("request_approval_states.status"),
                DB::raw("request_approval_states.status = " . $pending . " AS is_pending"),
                DB::raw("MAX(request_approval_states.status = " . $declined . ") OVER(PARTITION BY requestable_id) AS at_least_one_declined"),
                DB::raw("LAG(request_approval_states.status) OVER(PARTITION BY requestable_id ORDER BY request_approval_states.order) AS previous_status"),
            ]);

        //At least one declined and previous status
        $queryBuilder = $this->queryAsSub($queryBuilder, 'sub')
            ->where(function ($clause) use ($filters) {
                $clause->where('sub.requestable_account_id', $filters->account_id)
                    ->whereIn('sub.requestable_company_id', $filters->associated_companies);
            })
            ->select([
                DB::raw("sub.id"),
                DB::raw("sub.requestable_type"),
                DB::raw("sub.requestable_id"),
                DB::raw("sub.requestable_account_id"),
                DB::raw("sub.requestable_company_id"),
                DB::raw("sub.requestable_number"),
                DB::raw("sub.requestable_date_requested"),
                DB::raw("sub.order"),
                DB::raw("sub.approver_id"),
                DB::raw("sub.remarks"),
                DB::raw("sub.status"),
                DB::raw("
                    IF(NOT sub.at_least_one_declined,
                        IF(sub.is_pending AND (sub.previous_status IS NULL OR sub.previous_status IN(" . $approved . ")), 1, null)
                    , null) AS current_state_flag
                "),
            ]);

        //Show only the current pending approval flag
        if(isset($filters->show_only_current_state) && $filters->show_only_current_state){
            $queryBuilder = $this->queryAsSub($queryBuilder, 'current_state_flag_sub')
                ->select([
                    DB::raw("current_state_flag_sub.id"),
                    DB::raw("current_state_flag_sub.requestable_type"),
                    DB::raw("current_state_flag_sub.requestable_id"),
                    DB::raw("current_state_flag_sub.requestable_account_id"),
                    DB::raw("current_state_flag_sub.requestable_company_id"),
                    DB::raw("current_state_flag_sub.requestable_number"),
                    DB::raw("current_state_flag_sub.requestable_date_requested"),
                    DB::raw("current_state_flag_sub.order"),
                    DB::raw("current_state_flag_sub.approver_id"),
                    DB::raw("current_state_flag_sub.remarks"),
                    DB::raw("current_state_flag_sub.status"),
                    DB::raw("current_state_flag_sub.current_state_flag"),
                ])
                ->whereRaw("current_state_flag_sub.current_state_flag IS NOT NULL");
        }

        $queryBuilder = $this->queryAsSub($queryBuilder, 'request_approval_states_sub')
            ->select('request_approval_states_sub.*');

        $companyUserQueryBuilder = App::make(CompanyUserRepository::class)->baseQueryBuilder($companyUserRepositoryFilter, []);

        $queryBuilder = $queryBuilder
            ->joinSub($companyUserQueryBuilder, 'company_user_sub', function ($join) {
                $join->on('company_user_sub.user_id', '=', 'request_approval_states_sub.approver_id');
            })
            ->when(!empty($filters->user_ids) && is_array($filters->user_ids), function ($builder) use ($filters) {
                $builder->whereIn(DB::raw("request_approval_states_sub.approver_id"), $filters->user_ids);
            })
            ->when($filters->search ?? false, function ($builder, $value) {
                $builder->where(function ($clause) use ($value) {
                    $clause->where('request_approval_states_sub.requestable_number', 'LIKE', "%$value%");
                });
            })
            ->when(!empty($filters->requestable_type), function ($builder) use ($filters) {
                $builder->where('request_approval_states_sub.requestable_type', $filters->requestable_type);
            })
            ->when(!empty($filters->requestable_ids) && is_array($filters->requestable_ids), function ($builder) use ($filters) {
                $builder->whereIn('request_approval_states_sub.requestable_id', $filters->requestable_ids);
            })
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'request_approval_states_sub.id',
                'request_approval_states_sub.requestable_type',
                'request_approval_states_sub.requestable_id',
                'request_approval_states_sub.requestable_account_id',
                'request_approval_states_sub.requestable_company_id',
                'request_approval_states_sub.requestable_number',
                DB::raw("CONVERT_TZ(request_approval_states_sub.requestable_date_requested, 'UTC', company_user_sub.company_timezone) AS requestable_date_requested"),
                'request_approval_states_sub.order AS request_approval_state_order',
                'request_approval_states_sub.approver_id AS request_approval_state_approver_id',
                'request_approval_states_sub.remarks AS request_approval_state_remarks',
                'request_approval_states_sub.status AS request_approval_state_status',
                'request_approval_states_sub.current_state_flag AS request_approval_state_current_state_flag',
                'company_user_sub.*'
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'request_approval_states_sub.requestable_id', 'direction' => 'DESC'],
            ['field' => 'request_approval_states_sub.order', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }

    public function list($filters): Collection
    {
        $orders = [
            ['field' => 'request_approval_states_sub.requestable_id', 'direction' => 'DESC'],
            ['field' => 'request_approval_states_sub.order', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }
}
