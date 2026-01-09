<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Enums\Formulable;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\PolymorphicEmployeePayrollComponent\ListPolymorphicEmployeePayrollComponentRequest;
use App\Transformers\EmployeePayrollComponent\ItemTransformer;

class EmployeePayrollInfoController extends Controller
{
    public function __construct(
        protected EmployeePayrollComponentRepository $repository,
    ){}

    public function compensations(ListPolymorphicEmployeePayrollComponentRequest $request, $employeeUlid)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $filters->formulable_types = [Formulable::EARNINGS];
            $filters->employee_ulids = [$employeeUlid];

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->list($filters),
                ItemTransformer::class,
                'compensations'
            ));
        }

        abort(404);
    }

    public function deductions(ListPolymorphicEmployeePayrollComponentRequest $request, $employeeUlid)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $filters->formulable_types = [Formulable::DEDUCTIONS];
            $filters->employee_ulids = [$employeeUlid];

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->list($filters),
                ItemTransformer::class,
                'deductions'
            ));
        }

        abort(404);
    }

    public function incomeTaxes(ListPolymorphicEmployeePayrollComponentRequest $request, $employeeUlid)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $filters->formulable_types = [Formulable::INCOME_TAX];
            $filters->employee_ulids = [$employeeUlid];

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->list($filters),
                ItemTransformer::class,
                'income_taxes'
            ));
        }

        abort(404);
    }
}
