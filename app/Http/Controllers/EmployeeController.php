<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Employee\BatchUpdateEmployeeRequest;
use App\Http\Requests\Employee\ListEmployeeRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Requests\Employee\ViewEmployeeRequest;
use App\Transformers\Employee\ItemTransformer;
use App\Transformers\Employee\ListTransformer;
use App\Transformers\Employee\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmployeeController extends Controller
{
    public function indexGate(ListEmployeeRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function index(ListEmployeeRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $relations = ['user', 'department', 'designation', 'current_employment_profile', 'current_shift'];

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(EmployeeRepository::class)->paginate($filters, $relations),
                ListTransformer::class,
            ));
        }

        abort(404);
    }

    public function validate(StoreEmployeeRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employee' => Fractal::item(
                    App::make(EmployeeRepository::class)->hydrateItem($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function store(StoreEmployeeRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employee' => Fractal::item(
                    App::make(EmployeeRepository::class)->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateEmployeeRequest $request, $employeeId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employee' => Fractal::item(
                    App::make(EmployeeRepository::class)->update($employeeId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function batchUpdate(BatchUpdateEmployeeRequest $request)
    {
        if($request->expectsJson()){

            $employeeIdentifiers = data_get($request->validated(), 'employee_identifiers', []);
            $attributes = collect($request->validated())->except(['employee_identifiers'])->toArray();

            $batchUpdateErrors = App::make(EmployeeRepository::class)->batchUpdate($employeeIdentifiers, $attributes);

            return ResponseJson::successfulResponse([
                'batch_update_errors' => $batchUpdateErrors
            ]);
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    App::make(EmployeeRepository::class)->selection($filters),
                    SelectionTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function show(ViewEmployeeRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $employee = App::make(EmployeeRepository::class)->show($ulid);
            $employee = $employee ? Fractal::item($employee, ItemTransformer::class) : $employee;

            return ResponseJson::successfulResponse(['employee' => $employee]);
        }

        abort(404);
    }

    public function showGate(ViewEmployeeRequest $request, $ulid)
    {
        if($request->expectsJson()){

            $employee = App::make(EmployeeRepository::class)->showAndTransformToBasic($ulid);

            return ResponseJson::successfulResponse(['employee' => $employee]);
        }

        abort(404);
    }
}
