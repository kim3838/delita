<?php

namespace App\Http\Controllers;

use App\Concrete\LeaveService;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveBalanceMap\LeaveBalanceMapRequest;
use App\Http\Requests\LeaveBalanceMap\LeaveBalanceMinimumDateRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Traits\HasTime;
use Carbon\Carbon;

class LeaveBalanceMapController extends Controller
{
    public function __construct(
        protected readonly LeaveService $service
    ){}

    use HasTime;

    public function index(LeaveBalanceMapRequest $request)
    {
        if($request->expectsJson()){

            $employee = Employee::query()->findOrFail($request->validated()['employee_id']);
            $leaveType = LeaveType::query()->findOrFail($request->validated()['leave_type_id']);
            $upToDate = $request->validated()['up_to_date'];

            return ResponseJson::successfulResponse([
                'balance_map' => $this->service->getBalanceMap($employee, $leaveType, $upToDate),
            ]);
        }

        abort(404);
    }

    public function minimumDate(LeaveBalanceMinimumDateRequest $request)
    {
        if($request->expectsJson()){

            $employee = Employee::query()->findOrFail($request->validated()['employee_id']);
            $leaveType = LeaveType::query()->findOrFail($request->validated()['leave_type_id']);
            $date = $request->validated()['date'];

            $minimumDateParsed = $this->getDateIfGteMinimum(
                Carbon::parse($date),
                $this->service->getMinimumUpToDate($employee, $leaveType)
            );

            return ResponseJson::successfulResponse([
                'minimum_date' => $minimumDateParsed->format('Y-m-d')
            ]);
        }

        abort(404);
    }
}
