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
        $employee = Employee::query()->find(4);
        $leave = LeaveType::query()->find(4);

//        _debug([
//            'running_balance_by_date' => new LeaveService()->getRunningBalanceByDate($employee, $leave, '2027-12-01'),
//        ]);

        _debug([
            'grouped_decoded_by_year_monthly_balance' => new LeaveService()->getBalanceMap($employee, $leave, '2027-12-01'),
        ]);

        //new LeaveService()->debugBalanceMapBySingleLinePerDateSeries($employee, $leave, '2029-12-31');
    }
}
