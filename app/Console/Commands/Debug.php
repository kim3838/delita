<?php

namespace App\Console\Commands;

use App\Blueprint\Repositories\CompanyUserRolePermissionRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Concrete\LeaveService;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Console\Command;

class Debug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

    }

    private function companyUserRolePermission()
    {
        $filters = (object)[
            'account_id' => 2,
            'associated_company' => 4,
            'user_id' => 3,
            'permission_keys' => [
                'approve-any-request',
                'view-leave-running-balance'
            ]
        ];

        app(CompanyUserRolePermissionRepository::class)->list($filters);
    }

    private function requestApprovalState()
    {
        $filters = (object)[
            'account_id' => 2,
            'associated_companies' => [4],
            'requestable_type' => 'attendance_adjustment_request',
            'requestable_ids' => [19],
            'user_ids' => [],//14
            'show_only_current_state' => false
        ];

        app(RequestApprovalStateRepository::class)->baseQueryBuilder($filters);
    }

    private function leaveRunningBalance($debug)
    {
        $employee = Employee::query()->find(4);
        $leaveType = LeaveType::query()->find(4);
        $leaveService = new LeaveService();

        match(true){
            $debug == 'running_balance_by_date' => $leaveService->getRunningBalanceByDate($employee, $leaveType, '2027-12-01'),
            $debug == 'balance_by_period_series' => $leaveService->getBalancePeriodSeries($employee, $leaveType, '2029-12-31'),
            $debug == 'debug_single_line_per_date_series' => $leaveService->debugBalanceMapBySingleLinePerDateSeries($employee, $leaveType, '2029-12-31')
        };
    }
}
