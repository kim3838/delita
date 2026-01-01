<?php

namespace App\Http\Controllers;

use App\Concrete\LeaveService;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveRunningBalance\LeaveRunningBalancePeriodSeriesRequest;
use App\Http\Requests\LeaveRunningBalance\LeaveRunningBalanceMinimumDateRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Traits\HasTime;
use Carbon\Carbon;

class LeaveRunningBalancePeriodSeriesController extends Controller
{
    public function __construct(
        protected readonly LeaveService $service
    ){}

    use HasTime;

    public function index(LeaveRunningBalancePeriodSeriesRequest $request)
    {
        if($request->expectsJson()){

            $employee = Employee::query()->findOrFail($request->validated()['employee_id']);
            $leaveType = LeaveType::query()->findOrFail($request->validated()['leave_type_id']);
            $upToDate = $request->validated()['up_to_date'];
            $balancePeriodSeries = $this->service->getBalancePeriodSeries($employee, $leaveType, $upToDate);

            _clear_debug();
            _debug([
                '$balancePeriodSeries' => $balancePeriodSeries
            ]);

            return ResponseJson::successfulResponse([
                'balance_period_series' => $balancePeriodSeries,
            ]);
        }

        abort(404);
    }

    public function minimumDate(LeaveRunningBalanceMinimumDateRequest $request)
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
