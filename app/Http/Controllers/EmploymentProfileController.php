<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmploymentProfile\StoreEmploymentProfileRequest;
use App\Http\Requests\EmploymentProfile\UpdateEmploymentProfileRequest;
use App\Transformers\EmploymentProfile\ItemTransformer;
use App\Transformers\EmploymentProfile\ListTransformer;
use App\Transformers\EmploymentProfile\ValidatedTransformer;
use Illuminate\Http\Request;

class EmploymentProfileController extends Controller
{
    public function __construct(
        protected readonly EmploymentProfileRepository $repository
    ){}

    public function index(Request $request)
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

    public function validate(StoreEmploymentProfileRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employment_profile' => Fractal::item(
                    $this->repository->hydrateItem($request->validated()),
                    ValidatedTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function store(StoreEmploymentProfileRequest $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'employment_profile' => Fractal::item(
                    $this->repository->store($request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateEmploymentProfileRequest $request, $employmentProfileId)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse([
                'payroll_component' => Fractal::item(
                    $this->repository->update($employmentProfileId, $request->validated()),
                    ItemTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(Request $request, $employmentProfileId)
    {
        if($request->expectsJson()){

            $this->repository->delete($employmentProfileId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
