<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeIdentificationRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\EmployeeIdentification\ListEmployeeIdentificationRequest;
use App\Transformers\EmployeeEmployeeIdentification\ListTransformer;
use Illuminate\Support\Arr;
use stdClass;

class EmployeeEmployeeIdentificationController extends Controller
{
    public function __construct(
        protected readonly EmployeeIdentificationRepository $repository
    ){}

    public function index(ListEmployeeIdentificationRequest $request, $employeeId)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters')) ?? new stdClass();
            $filters->employee_ids = Arr::wrap($employeeId);

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->list($filters),
                ListTransformer::class,
                'identifications'
            ));
        }

        abort(404);
    }
}
