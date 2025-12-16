<?php

namespace App\Console\Commands;

use App\Concrete\LeaveService;
use App\Models\EmployeeLeaveType;
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
        $employeeLeaveType = EmployeeLeaveType::query()->find(3);

        new LeaveService()->getBalanceMap($employeeLeaveType, '2029-12-31');
    }
}
