<?php

namespace App\Transformers\LeaveBalanceByType;

use App\Concrete\LeaveService;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

class BasicTransformer extends TransformerAbstract
{
    public function transform(Employee $employee): array
    {
        $leaveBalanceByType = [];

        $date = $employee->leave_balance_by_type_date;

        foreach ($employee->leave_balance_by_type_ulids as $ulid){

            $leaveType = LeaveType::query()->where('ulid', $ulid)->firstOrFail();

            $dateSeries = App::make(LeaveService::class)->getRunningBalanceByDate($employee, $leaveType, $date);

            $leaveBalanceByType[$ulid] = (float)$dateSeries['running_balance'];
        }

        return $leaveBalanceByType;
    }
}
