<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Concrete\LeaveService;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Transformers\LeaveBalanceByType\BasicTransformer as LeaveBalanceByTypeBasicTransformer;
use App\Transformers\LeaveType\BasicTransformer;
use Illuminate\Http\Request;

class LeaveRunningBalanceByTypeController extends Controller
{
    public function __construct(
        protected EmployeeRepository $employeeRepository,
        protected LeaveTypeRepository $leaveTypeRepository,
        protected LeaveService $leaveService,
        protected EmploymentProfileRepository $employmentProfileRepository
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $date = $filters->date;

            $leaveTypes = $this->leaveTypeRepository->list((object)[
                'company_id' => $filters->company_id,
                'ids' => $filters->leave_type_ids ?? [],
            ]);

            $employee = $this->employeeRepository->show($filters->employee_ulid);
            $employee->leave_balance_by_type_date = $date;
            $employee->leave_balance_by_type_ulids = $leaveTypes->pluck('ulid')->toArray();

            $employeeLeaveBalanceByTypes = Fractal::item($employee, LeaveBalanceByTypeBasicTransformer::class);

            $leaveTypes = $leaveTypes->map(function($leaveType) use ($employeeLeaveBalanceByTypes){

                $basicLeaveType = Fractal::item($leaveType, BasicTransformer::class);

                return [
                    ...$basicLeaveType,
                    'running_balance' => $employeeLeaveBalanceByTypes[$basicLeaveType['ulid']] ?? 0
                ];
            });

            return ResponseJson::successfulResponse([
                'leave_balance_by_types' => $leaveTypes
            ]);
        }

        abort(404);
    }
}
