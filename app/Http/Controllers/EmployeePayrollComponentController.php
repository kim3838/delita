<?php

namespace App\Http\Controllers;

use App\Blueprint\EnumInterface;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\PolymorphicEmployeePayrollComponent\DestroyPolymorphicEmployeePayrollComponentRequest;
use App\Http\Requests\PolymorphicEmployeePayrollComponent\StorePolymorphicEmployeePayrollComponentRequest;
use App\Http\Requests\PolymorphicEmployeePayrollComponent\UpdatePolymorphicEmployeePayrollComponentRequest;
use App\Transformers\Compensation\SelectionAsComponentableMorphTransformer as CompensationSelectionAsComponentableMorphTransformer;
use App\Transformers\Deduction\SelectionAsComponentableMorphTransformer as DeductionSelectionAsComponentableMorphTransformer;
use App\Transformers\EmployeePayrollComponent\ItemTransformer;
use App\Transformers\EmployeePayrollComponent\ListTransformer;
use App\Transformers\EmployeePayrollComponent\ValidatedTransformer;
use App\Transformers\IncomeTax\SelectionAsComponentableMorphTransformer as IncomeTaxSelectionAsComponentableMorphTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmployeePayrollComponentController extends Controller
{
    public function __construct(
        protected EmployeePayrollComponentRepository $repository,
    ){}

    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    $this->repository->list($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function validate(StorePolymorphicEmployeePayrollComponentRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'payroll_component' => Fractal::item(
                    $this->repository->hydrateItem($request->validated()),
                    ValidatedTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function store(StorePolymorphicEmployeePayrollComponentRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'payroll_component' => Fractal::item(
                    $this->repository->store($request->validated()),
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
                    $this->repository->update($employeePayrollComponentId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroyPolymorphicEmployeePayrollComponentRequest $request, $employeePayrollComponentId)
    {
        if($request->expectsJson()){

            $this->repository->delete($employeePayrollComponentId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function payrollComponentType(Request $request)
    {
        if($request->expectsJson()){

            $compensations = App::make(EnumInterface::class)->selection('compensation');
            $deductions = App::make(EnumInterface::class)->selection('deduction');
            $incomeTaxes = App::make(EnumInterface::class)->selection('income_tax');

            return ResponseJson::successfulResponse([
                'selection' => array_merge($compensations::all(), $deductions::all(), $incomeTaxes::all())
            ]);
        }
    }

    public function payrollComponentName(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $compensations = Fractal::collection(App::make(CompensationRepository::class)->selection($filters), CompensationSelectionAsComponentableMorphTransformer::class)['data'];
            $deductions = Fractal::collection(App::make(DeductionRepository::class)->selection($filters), DeductionSelectionAsComponentableMorphTransformer::class)['data'];
            $incomeTaxes = Fractal::collection(App::make(IncomeTaxRepository::class)->selection($filters), IncomeTaxSelectionAsComponentableMorphTransformer::class)['data'];

            return ResponseJson::successfulResponse([
                'selection' => array_merge($compensations, $deductions, $incomeTaxes)
            ]);
        }
    }
}
