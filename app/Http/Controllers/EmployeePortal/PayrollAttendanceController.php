<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\AttendanceRepository;
use App\Blueprint\Repositories\PayrollRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePortal\PayrollAttendance\ListPayrollAttendanceRequest;
use App\Transformers\Attendance\ListTransformer;

class PayrollAttendanceController extends Controller
{
    public function __construct(
        protected AttendanceRepository $repository,
    ){}

    public function index(ListPayrollAttendanceRequest $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $payroll = app(PayrollRepository::class)->list((object)[
                'payroll_ids' => [$filters->payroll_id]
            ]);

            if($payroll){
                $filters->date_from = $payroll->first()->start_date->toDateString();
                $filters->date_to = $payroll->first()->end_date->toDateString();
            }

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->paginate($filters, ['salary_statement_attendance']),
                ListTransformer::class
            ));
        }

        abort(404);
    }
}
