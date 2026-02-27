<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementAttendanceRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatement\ListSalaryStatementRequest;
use App\Transformers\SalaryStatementAttendance\ListTransformer;

class SalaryStatementAttendanceController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementAttendanceRepository $repository
    ){}

    public function index(ListSalaryStatementRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            if(!empty($filters->assigned_employee_group_ids)){
                $filters->or_employee_ids = $filters->employee_ids;
                unset($filters->employee_ids);
            }

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, ['salary_statement', 'payroll_components']), ListTransformer::class
            ));
        }

        abort(404);
    }
}
