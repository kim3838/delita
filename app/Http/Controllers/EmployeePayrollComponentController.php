<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\PolymorphicEmployeePayrollComponent\DestroyPolymorphicEmployeePayrollComponentRequest;
use App\Http\Requests\PolymorphicEmployeePayrollComponent\StorePolymorphicEmployeePayrollComponentRequest;
use App\Http\Requests\PolymorphicEmployeePayrollComponent\UpdatePolymorphicEmployeePayrollComponentRequest;
use App\Transformers\EmployeePayrollComponent\ItemTransformer;
use App\Transformers\EmployeePayrollComponent\ValidatedTransformer;
use Illuminate\Support\Facades\App;

class EmployeePayrollComponentController extends Controller
{
    public function index($employeeUlid)
    {
        if(request()->expectsJson()){

            $payrollComponents = App::make(EmployeePayrollComponentRepository::class)->list($employeeUlid);

            $data = [
                'compensations' => Fractal::collection($payrollComponents['compensations'], ItemTransformer::class),
                'deductions' => Fractal::collection($payrollComponents['deductions'], ItemTransformer::class),
                'income_taxes' => Fractal::collection($payrollComponents['income_taxes'], ItemTransformer::class),
            ];

            return ResponseJson::successfulResponse($data);
        }

        abort(404);
    }

    public function validate(StorePolymorphicEmployeePayrollComponentRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'payroll_component' => Fractal::item(
                    App::make(EmployeePayrollComponentRepository::class)->hydrateItem($request->validated()),
                    ValidatedTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function compensations($employeeUlid)
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(EmployeePayrollComponentRepository::class)->compensations($employeeUlid),
                ItemTransformer::class,
                'compensations'
            ));
        }

        abort(404);
    }

    public function deductions($employeeUlid)
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(EmployeePayrollComponentRepository::class)->deductions($employeeUlid),
                ItemTransformer::class,
                'deductions'
            ));
        }

        abort(404);
    }

    public function incomeTaxes($employeeUlid)
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(EmployeePayrollComponentRepository::class)->incomeTaxes($employeeUlid),
                ItemTransformer::class,
                'income_taxes'
            ));
        }

        abort(404);
    }

    public function store(StorePolymorphicEmployeePayrollComponentRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'payroll_component' => Fractal::item(
                    App::make(EmployeePayrollComponentRepository::class)->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdatePolymorphicEmployeePayrollComponentRequest $request, $employeePayrollComponentId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'payroll_component' => Fractal::item(
                    App::make(EmployeePayrollComponentRepository::class)->update($employeePayrollComponentId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroyPolymorphicEmployeePayrollComponentRequest $request, $employeePayrollComponentId)
    {
        if($request->expectsJson()){

            App::make(EmployeePayrollComponentRepository::class)->delete($employeePayrollComponentId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
