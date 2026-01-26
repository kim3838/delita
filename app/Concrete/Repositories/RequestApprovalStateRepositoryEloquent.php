<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Concrete\ApprovalService;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\RequestApprovalStatus;
use App\Exceptions\UnexpectedException;
use App\Models\RequestApprovalState;
use App\Traits\HasPolicy;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestApprovalStateRepositoryEloquent extends BaseRepositoryEloquent implements RequestApprovalStateRepository
{
    use HasPolicy;

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

        $approvedByCompanyUserRepositoryFilter = clone $filters;
        unset($approvedByCompanyUserRepositoryFilter->user_ids);
        unset($approvedByCompanyUserRepositoryFilter->search);

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
                    WHEN requestable_type = 'overtime_request' THEN
                        (SELECT companies.account_id FROM overtime_requests LEFT JOIN companies ON companies.id = overtime_requests.company_id WHERE overtime_requests.id = requestable_id)
                    WHEN requestable_type = 'leave_request' THEN
                        (SELECT companies.account_id FROM leave_requests LEFT JOIN companies ON companies.id = leave_requests.company_id WHERE leave_requests.id = requestable_id)
                    ELSE '' END AS requestable_account_id
                "),
                DB::raw("
                    CASE WHEN requestable_type = 'attendance_adjustment_request' THEN
                        (SELECT company_id FROM attendance_adjustment_requests WHERE id = requestable_id)
                    WHEN requestable_type = 'overtime_request' THEN
                        (SELECT company_id FROM overtime_requests WHERE id = requestable_id)
                    WHEN requestable_type = 'leave_request' THEN
                        (SELECT company_id FROM leave_requests WHERE id = requestable_id)
                    ELSE '' END AS requestable_company_id
                "),
                DB::raw("
                    CASE WHEN requestable_type = 'attendance_adjustment_request' THEN
                        (SELECT number FROM attendance_adjustment_requests WHERE id = requestable_id)
                    WHEN requestable_type = 'overtime_request' THEN
                        (SELECT number FROM overtime_requests WHERE id = requestable_id)
                    WHEN requestable_type = 'leave_request' THEN
                        (SELECT number FROM leave_requests WHERE id = requestable_id)
                    ELSE '' END AS requestable_number
                "),
                DB::raw("
                    CASE WHEN requestable_type = 'attendance_adjustment_request' THEN
                        (SELECT date_requested FROM attendance_adjustment_requests WHERE id = requestable_id)
                    WHEN requestable_type = 'overtime_request' THEN
                        (SELECT date_requested FROM overtime_requests WHERE id = requestable_id)
                    WHEN requestable_type = 'leave_request' THEN
                        (SELECT date_requested FROM leave_requests WHERE id = requestable_id)
                    ELSE '' END AS requestable_date_requested
                "),
                DB::raw("request_approval_states.order"),
                DB::raw("request_approval_states.approver_id"),
                DB::raw("request_approval_states.approved_by"),
                DB::raw("request_approval_states.remarks"),
                DB::raw("request_approval_states.status"),
                DB::raw("request_approval_states.approved_at"),
                DB::raw("request_approval_states.status = " . $pending . " AS is_pending"),
                DB::raw("MAX(request_approval_states.status = " . $declined . ") OVER(PARTITION BY requestable_type, requestable_id ORDER BY requestable_type, requestable_id, `order`) AS at_least_one_declined"),
                DB::raw("LAG(request_approval_states.status) OVER(PARTITION BY requestable_type, requestable_id ORDER BY requestable_type, requestable_id, `order`) AS previous_status"),
            ]);

        $this->setOrdersOnBuilder($queryBuilder, [
            ['field' => 'request_approval_states.requestable_type', 'direction' => 'ASC'],
            ['field' => 'request_approval_states.requestable_id', 'direction' => 'ASC'],
            ['field' => 'request_approval_states.order', 'direction' => 'ASC'],
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
                DB::raw("sub.approved_by"),
                DB::raw("sub.remarks"),
                DB::raw("sub.status"),
                DB::raw("sub.approved_at"),
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
                    DB::raw("current_state_flag_sub.approved_by"),
                    DB::raw("current_state_flag_sub.remarks"),
                    DB::raw("current_state_flag_sub.status"),
                    DB::raw("current_state_flag_sub.approved_at"),
                    DB::raw("current_state_flag_sub.current_state_flag"),
                ])
                ->whereRaw("current_state_flag_sub.current_state_flag IS NOT NULL");
        }

        $queryBuilder = $this->queryAsSub($queryBuilder, 'request_approval_states_sub')
            ->select('request_approval_states_sub.*');

        $companyUserQueryBuilder = App::make(CompanyUserRepository::class)->baseQueryBuilder($companyUserRepositoryFilter, []);

        $approvedByCompanyUserQueryBuilder = App::make(CompanyUserRepository::class)->baseQueryBuilder($approvedByCompanyUserRepositoryFilter, []);

        $queryBuilder = $queryBuilder
            ->joinSub($companyUserQueryBuilder, 'company_user_sub', function ($join) {
                $join->on('company_user_sub.user_id', '=', 'request_approval_states_sub.approver_id');
            })
            ->leftJoinSub($approvedByCompanyUserQueryBuilder, 'approved_by_company_user_sub', function ($join) {
                $join->on('approved_by_company_user_sub.user_id', '=', 'request_approval_states_sub.approved_by');
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
                'request_approval_states_sub.approved_by AS request_approval_state_approved_by',
                'request_approval_states_sub.remarks AS request_approval_state_remarks',
                'request_approval_states_sub.status AS request_approval_state_status',
                DB::raw("CONVERT_TZ(request_approval_states_sub.approved_at, 'UTC', company_user_sub.company_timezone) AS request_approval_state_approved_at"),
                'request_approval_states_sub.current_state_flag AS request_approval_state_current_state_flag',
                'company_user_sub.*',
                'approved_by_company_user_sub.company_timezone AS approved_by_user_company_timezone',
                'approved_by_company_user_sub.user_id AS approved_by_user_id',
                'approved_by_company_user_sub.user_username AS approved_by_user_username',
                'approved_by_company_user_sub.is_employee AS approved_by_user_is_employee',
                'approved_by_company_user_sub.company_employee_number AS approved_by_user_company_employee_number',
                'approved_by_company_user_sub.company_employee_family_name AS approved_by_user_company_employee_family_name',
                'approved_by_company_user_sub.company_employee_middle_name AS approved_by_user_company_employee_middle_name',
                'approved_by_company_user_sub.company_employee_given_name AS approved_by_user_company_employee_given_name',
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'request_approval_states_sub.requestable_date_requested', 'direction' => 'DESC'],
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
            ['field' => 'request_approval_states_sub.requestable_date_requested', 'direction' => 'DESC'],
            ['field' => 'request_approval_states_sub.requestable_id', 'direction' => 'DESC'],
            ['field' => 'request_approval_states_sub.order', 'direction' => 'ASC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    /**
     * @throws UnexpectedException
     */
    public function applyWorkflow($accountId, RequestApprovalStatus $action, $remarks, $approvalStates): array
    {
        $approvalStates = collect($approvalStates)->sortBy('id')->toArray();
        $actionReadable = $action->verbLabel();
        $results = [];

        foreach($approvalStates as $approvalState){

            $approvalStateId = $approvalState['id'];
            $requestableNumber = $approvalState['number'];

            $approvalState = RequestApprovalState::query()->find($approvalStateId);

            if(empty($approvalState)){
                $results[] = [
                    'number' => $requestableNumber,
                    'resolved' => false,
                    'error' => 'Approval state not found.'
                ];

                continue;
            }

            if(!empty($approvalState->approved_by)){
                $results[] = [
                    'number' => $requestableNumber,
                    'resolved' => false,
                    'error' => 'Approval state already exist.'
                ];

                continue;
            }

            $userIsTheApprover = $approvalState->approver_id == Auth::id();
            $userHasPermissionToApplyWorkFlow = match($action){
                RequestApprovalStatus::APPROVED => $this->hasPermission(Auth::user(), 'approve-any-request', $accountId),
                RequestApprovalStatus::DECLINED => $this->hasPermission(Auth::user(), 'decline-any-request', $accountId),
                default => false,
            };

            if(!$userIsTheApprover && !$userHasPermissionToApplyWorkFlow){
                $results[] = [
                    'number' => $requestableNumber,
                    'resolved' => false,
                    'error' => 'You are not authorized to ' . strtolower($actionReadable) . ' this request.'
                ];

                continue;
            }

            $approvalService = new ApprovalService();

            list(
                $noValidationError,
                $validationError
            ) = $approvalService->chainRequestableWorkflow($action, $approvalState);

            /**
             * If no validation error, update approval state
             * */
            if($noValidationError){
                $this->update($approvalStateId, [
                    'approved_by' => Auth::id(),
                    'remarks' => $remarks,
                    'status' => $action->value,
                    'approved_at' => Carbon::now()->toDateTimeString()
                ]);
            }

            $results[] = [
                'number' => $requestableNumber,
                'resolved' => $noValidationError,
                'error' => $validationError
            ];
        }

        return $results;
    }
}
