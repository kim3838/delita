<?php

namespace App\Http\Controllers;

use App\Concrete\LeaveService;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveBalance\LeaveBalanceRequest;
use App\Models\Employee;
use App\Models\LeaveType;

class LeaveBalanceController extends Controller
{
    public function __construct(
        protected readonly LeaveService $service
    ){}

    public function index(LeaveBalanceRequest $request)
    {
        if($request->expectsJson()){

            $employee = Employee::query()->findOrFail($request->validated()['employee_id']);
            $leaveType = LeaveType::query()->findOrFail($request->validated()['leave_type_id']);
            $date = $request->validated()['date'];

            return ResponseJson::successfulResponse([
                'date_series' => $this->service->getRunningBalanceByDate($employee, $leaveType, $date),
                'limit_reached' => $this->service->isLimitReached($employee, $leaveType, $date),
            ]);
        }

        abort(404);
    }

}
