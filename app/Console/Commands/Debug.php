<?php

namespace App\Console\Commands;

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
