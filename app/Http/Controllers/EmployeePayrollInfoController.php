<?php

namespace App\Http\Controllers;

use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Models\Employee;
use App\Transformers\EmployeePayrollComponent\ItemTransformer;

class EmployeePayrollInfoController extends Controller
{
    public function compensations($employeeUlid)
    {
        if(request()->expectsJson()){

            $compensations = Employee::query()->where('ulid', $employeeUlid)->firstOrFail()->compensations;

            return ResponseJson::successfulResponse(Fractal::collection(
                $compensations,
                ItemTransformer::class,
                'compensations'
            ));
        }

        abort(404);
    }

    public function deductions($employeeUlid)
    {
        if(request()->expectsJson()){

            $deductions = Employee::query()->where('ulid', $employeeUlid)->firstOrFail()->deductions;

            return ResponseJson::successfulResponse(Fractal::collection(
                $deductions,
                ItemTransformer::class,
                'deductions'
            ));
        }

        abort(404);
    }

    public function incomeTaxes($employeeUlid)
    {
        if(request()->expectsJson()){

            $incomeTaxes = Employee::query()->where('ulid', $employeeUlid)->firstOrFail()->incomeTaxes;

            return ResponseJson::successfulResponse(Fractal::collection(
                $incomeTaxes,
                ItemTransformer::class,
                'income_taxes'
            ));
        }

        abort(404);
    }
}
