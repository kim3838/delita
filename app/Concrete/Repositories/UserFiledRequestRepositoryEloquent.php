<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\AttendanceAdjustmentRequestRepository;
use App\Blueprint\Repositories\CompanyUserRepository;
use App\Blueprint\Repositories\LeaveRequestRepository;
use App\Blueprint\Repositories\OvertimeRequestRepository;
use App\Blueprint\Repositories\UserFiledRequestRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Hydrations\User\UserFiledRequest;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class UserFiledRequestRepositoryEloquent extends BaseRepositoryEloquent implements UserFiledRequestRepository
{
    public function model(): string
    {
        return UserFiledRequest::class;
    }

    public function baseQueryBuilder($filters, $orders = []): QueryBuilder
    {
        $companyUserRepositoryFilter = clone $filters;
        if(isset($companyUserRepositoryFilter->user_ids)){
            $companyUserRepositoryFilter->pre_selected_user_ids = $companyUserRepositoryFilter->user_ids;
        }
        unset($companyUserRepositoryFilter->user_ids);
        unset($companyUserRepositoryFilter->search);
        unset($companyUserRepositoryFilter->statuses);

        $companyUserQueryBuilder = App::make(CompanyUserRepository::class)->baseQueryBuilder($companyUserRepositoryFilter, []);

        $requestableFilter = clone $filters;

        /**
         * All user attendance adjustment requests
         * */
        $attendanceAdjustmentRequestBuilder = App::make(AttendanceAdjustmentRequestRepository::class)->baseQueryBuilder($requestableFilter, []);

        $attendanceAdjustmentRequestBuilder = $this->queryAsSub($companyUserQueryBuilder, 'company_user_sub')
            ->joinSub($attendanceAdjustmentRequestBuilder, 'attendance_adjustment_request_sub', function ($join) {
                $join->on('attendance_adjustment_request_sub.requested_by', '=', 'company_user_sub.user_id');
            })
            ->where(function ($clause) use ($filters) {
                $clause->whereIn('attendance_adjustment_request_sub.company_id', $filters->associated_companies);
            })
            ->select([
                'company_user_sub.user_id',
                'company_user_sub.user_ulid',
                'company_user_sub.user_username',
                'company_user_sub.user_email',
                'company_user_sub.user_status',
                'company_user_sub.user_email_verified_at',
                'company_user_sub.user_timezone',
                'company_user_sub.company_id AS user_company_id',
                'company_user_sub.company_name',
                'company_user_sub.company_timezone',
                'company_user_sub.company_assignment_type',
                'company_user_sub.is_employee',
                'company_user_sub.company_employee_number',
                'company_user_sub.company_employee_full_name',

                'attendance_adjustment_request_sub.company_id AS company_id',
                DB::raw("'attendance_adjustment_request' AS requestable_type"),
                'attendance_adjustment_request_sub.id AS requestable_id',
                'attendance_adjustment_request_sub.number AS number',
                'attendance_adjustment_request_sub.date_requested AS date_requested',
                'attendance_adjustment_request_sub.remarks AS remarks',
                'attendance_adjustment_request_sub.status_summary AS status_summary',
            ]);

        $attendanceAdjustmentRequestBuilder = $this->queryAsSub($attendanceAdjustmentRequestBuilder, 'attendance_adjustment_requests')
            ->select([
                DB::raw("CONCAT(attendance_adjustment_requests.requestable_type, '_', attendance_adjustment_requests.requestable_id) AS id"),
                'attendance_adjustment_requests.requestable_type',
                'attendance_adjustment_requests.requestable_id',
                'attendance_adjustment_requests.number',
                'attendance_adjustment_requests.date_requested',
                'attendance_adjustment_requests.remarks',
                'attendance_adjustment_requests.status_summary',

                'attendance_adjustment_requests.user_id',
                'attendance_adjustment_requests.user_ulid',
                'attendance_adjustment_requests.user_username',
                'attendance_adjustment_requests.user_email',
                'attendance_adjustment_requests.user_status',
                'attendance_adjustment_requests.user_email_verified_at',
                'attendance_adjustment_requests.user_timezone',

                'attendance_adjustment_requests.company_id AS user_company_id',
                'attendance_adjustment_requests.company_name',
                'attendance_adjustment_requests.company_timezone',
                'attendance_adjustment_requests.company_assignment_type',
                'attendance_adjustment_requests.is_employee',
                'attendance_adjustment_requests.company_employee_number',
                'attendance_adjustment_requests.company_employee_full_name',
            ]);

        /**
         * All user overtime requests
         * */
        $overtimeRequestBuilder = App::make(OvertimeRequestRepository::class)->baseQueryBuilder($requestableFilter, []);

        $overtimeRequestBuilder = $this->queryAsSub($companyUserQueryBuilder, 'company_user_sub')
            ->joinSub($overtimeRequestBuilder, 'overtime_request_sub', function ($join) {
                $join->on('overtime_request_sub.requested_by', '=', 'company_user_sub.user_id');
            })
            ->where(function ($clause) use ($filters) {
                $clause->whereIn('overtime_request_sub.company_id', $filters->associated_companies);
            })
            ->select([
                'company_user_sub.user_id',
                'company_user_sub.user_ulid',
                'company_user_sub.user_username',
                'company_user_sub.user_email',
                'company_user_sub.user_status',
                'company_user_sub.user_email_verified_at',
                'company_user_sub.user_timezone',
                'company_user_sub.company_id AS user_company_id',
                'company_user_sub.company_name',
                'company_user_sub.company_timezone',
                'company_user_sub.company_assignment_type',
                'company_user_sub.is_employee',
                'company_user_sub.company_employee_number',
                'company_user_sub.company_employee_full_name',

                'overtime_request_sub.company_id AS company_id',
                DB::raw("'overtime_request' AS requestable_type"),
                'overtime_request_sub.id AS requestable_id',
                'overtime_request_sub.number AS number',
                'overtime_request_sub.date_requested AS date_requested',
                'overtime_request_sub.remarks AS remarks',
                'overtime_request_sub.status_summary AS status_summary',
            ]);

        $overtimeRequestBuilder = $this->queryAsSub($overtimeRequestBuilder, 'overtime_requests')
            ->select([
                DB::raw("CONCAT(overtime_requests.requestable_type, '_', overtime_requests.requestable_id) AS id"),
                'overtime_requests.requestable_type',
                'overtime_requests.requestable_id',
                'overtime_requests.number',
                'overtime_requests.date_requested',
                'overtime_requests.remarks',
                'overtime_requests.status_summary',

                'overtime_requests.user_id',
                'overtime_requests.user_ulid',
                'overtime_requests.user_username',
                'overtime_requests.user_email',
                'overtime_requests.user_status',
                'overtime_requests.user_email_verified_at',
                'overtime_requests.user_timezone',
                'overtime_requests.company_id AS user_company_id',
                'overtime_requests.company_name',
                'overtime_requests.company_timezone',

                'overtime_requests.company_assignment_type',
                'overtime_requests.is_employee',
                'overtime_requests.company_employee_number',
                'overtime_requests.company_employee_full_name',
            ]);

        /**
         * All user leave requests
         * */
        $leaveRequestBuilder = App::make(LeaveRequestRepository::class)->baseQueryBuilder($requestableFilter, []);

        $leaveRequestBuilder = $this->queryAsSub($companyUserQueryBuilder, 'company_user_sub')
            ->joinSub($leaveRequestBuilder, 'leave_request_sub', function ($join) {
                $join->on('leave_request_sub.requested_by', '=', 'company_user_sub.user_id');
            })
            ->where(function ($clause) use ($filters) {
                $clause->whereIn('leave_request_sub.company_id', $filters->associated_companies);
            })
            ->select([
                'company_user_sub.user_id',
                'company_user_sub.user_ulid',
                'company_user_sub.user_username',
                'company_user_sub.user_email',
                'company_user_sub.user_status',
                'company_user_sub.user_email_verified_at',
                'company_user_sub.user_timezone',
                'company_user_sub.company_id AS user_company_id',
                'company_user_sub.company_name',
                'company_user_sub.company_timezone',
                'company_user_sub.company_assignment_type',
                'company_user_sub.is_employee',
                'company_user_sub.company_employee_number',
                'company_user_sub.company_employee_full_name',

                'leave_request_sub.company_id AS company_id',
                DB::raw("'leave_request' AS requestable_type"),
                'leave_request_sub.id AS requestable_id',
                'leave_request_sub.number AS number',
                'leave_request_sub.date_requested AS date_requested',
                'leave_request_sub.remarks AS remarks',
                'leave_request_sub.status_summary AS status_summary',
            ]);

        $leaveRequestBuilder = $this->queryAsSub($leaveRequestBuilder, 'leave_requests')
            ->select([
                DB::raw("CONCAT(leave_requests.requestable_type, '_', leave_requests.requestable_id) AS id"),
                'leave_requests.requestable_type',
                'leave_requests.requestable_id',
                'leave_requests.number',
                'leave_requests.date_requested',
                'leave_requests.remarks',
                'leave_requests.status_summary',

                'leave_requests.user_id',
                'leave_requests.user_ulid',
                'leave_requests.user_username',
                'leave_requests.user_email',
                'leave_requests.user_status',
                'leave_requests.user_email_verified_at',
                'leave_requests.user_timezone',
                'leave_requests.company_id AS user_company_id',
                'leave_requests.company_name',
                'leave_requests.company_timezone',

                'leave_requests.company_assignment_type',
                'leave_requests.is_employee',
                'leave_requests.company_employee_number',
                'leave_requests.company_employee_full_name',
            ]);

        /**
         * All user requests
         * */
        $queryBuilder = $attendanceAdjustmentRequestBuilder
            ->unionAll($overtimeRequestBuilder)
            ->unionAll($leaveRequestBuilder);

        $queryBuilder = $this->queryAsSub($queryBuilder, 'user_filed_requests')
            ->select([
                DB::raw("ROW_NUMBER() OVER(".$this->rowNumberOrder($orders).") AS `row_number`"),
                'user_filed_requests.*'
            ]);

        $this->setOrdersOnBuilder($queryBuilder, $orders);

        return $queryBuilder;
    }

    public function paginate($filters): LengthAwarePaginator
    {
        $orders = [
            ['field' => 'date_requested', 'direction' => 'DESC'],
        ];

        $queryBuilder = $this->baseQueryBuilder($filters, $orders);

        $paginator = $this->createPaginationFromBuilder($queryBuilder);

        return $this->hydratePaginationItems($paginator, $this->model());
    }
}
