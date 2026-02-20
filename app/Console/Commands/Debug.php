<?php

namespace App\Console\Commands;

use App\Blueprint\PayrollServiceInterface;
use App\Blueprint\Repositories\CompanyUserRolePermissionRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Blueprint\Repositories\RequestApprovalStateRepository;
use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Blueprint\Repositories\UserFiledRequestRepository;
use App\Concrete\LeaveService;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Transformers\PayrollPayload\ListTransformer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

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

    private function salaryStatements()
    {
        $filters = (object)[
            'company_ids' => [4],
            'payroll_ids' => [12],
        ];

        $salaryStatements = App::make(SalaryStatementRepository::class)->paginate($filters);
    }

    private function payroll()
    {
        $payrollService = App::make(PayrollServiceInterface::class, [Company::find(4)]);
        $payroll = App::make(PayrollRepository::class)->model()::first();

        $payrollService->generateSalaryStatements($payroll);
    }

    private function userFiledRequest()
    {
        $filters = (object)[
            'account_id' => 2,
            'associated_companies' => [4],
            'user_ids' => [3],
            'statuses' => []
        ];

        app(UserFiledRequestRepository::class)->baseQueryBuilder($filters);
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
            'requestable_ids' => [],
            'user_ids' => [14],
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
