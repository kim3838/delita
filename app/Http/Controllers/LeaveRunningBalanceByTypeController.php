<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Blueprint\Repositories\LeaveTypeRepository;
use App\Concrete\LeaveService;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\LeaveRunningBalance\LeaveRunningBalanceByTypeRequest;
use App\Transformers\LeaveBalanceByType\ListTransformer;
use App\Transformers\LeaveType\BasicTransformer;

class LeaveRunningBalanceByTypeController extends Controller
{
    public function __construct(
        protected EmployeeRepository $employeeRepository,
        protected LeaveTypeRepository $leaveTypeRepository,
        protected LeaveService $leaveService,
        protected EmploymentProfileRepository $employmentProfileRepository
    ){}

    public function index(LeaveRunningBalanceByTypeRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $date = $filters->date;

            $leaveTypes = $this->leaveTypeRepository->list((object)[
                'company_id' => $filters->company_id,
                'ids' => $filters->leave_type_ids,
            ]);

            $employees = $this->employeeRepository->paginate($filters, ['current_employment_profile']);

            foreach ($employees->items() as $employee){
                $employee->leave_balance_by_type_date = $date;
                $employee->leave_balance_by_type_ulids = $leaveTypes->pluck('ulid')->toArray();
            }

            $employees = Fractal::collection($employees, ListTransformer::class);

            return ResponseJson::successfulResponse([
                'employees' => $employees,
                'leave_types' => $leaveTypes->map(fn($leaveType) => Fractal::item($leaveType, BasicTransformer::class))
            ]);
        }

        abort(404);
    }
}
