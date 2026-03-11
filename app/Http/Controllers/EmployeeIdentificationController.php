<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeIdentificationRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmployeeIdentification\BatchDestroyEmployeeIdentificationRequest;
use App\Http\Requests\EmployeeIdentification\ListEmployeeIdentificationRequest;
use App\Http\Requests\EmployeeIdentification\StoreEmployeeIdentificationRequest;
use App\Http\Requests\EmployeeIdentification\UpdateEmployeeIdentificationRequest;
use App\Transformers\EmployeeIdentification\ItemTransformer;
use App\Transformers\EmployeeIdentification\ListTransformer;
use App\Transformers\EmployeeIdentification\ValidatedTransformer;

class EmployeeIdentificationController extends Controller
{
    public function __construct(
        protected readonly EmployeeIdentificationRepository $repository
    ){}

    public function indexGate(ListEmployeeIdentificationRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }

    public function index(ListEmployeeIdentificationRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    $this->repository->paginate($filters),
                    ListTransformer::class
                )
            );
        }

        abort(404);
    }

    public function validate(StoreEmployeeIdentificationRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employee_identification' => Fractal::item(
                    $this->repository->hydrateItem($request->validated()),
                    ValidatedTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function store(StoreEmployeeIdentificationRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employee_identification' => Fractal::item(
                    $this->repository->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateEmployeeIdentificationRequest $request, $employeeIdentificationId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'payroll_component' => Fractal::item(
                    $this->repository->update($employeeIdentificationId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function batchDestroy(BatchDestroyEmployeeIdentificationRequest $request)
    {
        if($request->expectsJson()){

            $employmentProfileIds = data_get($request->validated(), 'employee_identification_ids', []);

            $this->repository->batchDelete($employmentProfileIds);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
