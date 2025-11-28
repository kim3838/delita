<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmploymentProfileRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\EmploymentProfile\ListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use stdClass;

class EmployeeEmploymentProfilesController extends Controller
{
    public function __construct(
        protected readonly EmploymentProfileRepository $repository
    ){}

    public function index(Request $request, $employeeId)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters')) ?? new stdClass();
            $filters->employee_ids = Arr::wrap($employeeId);

            return ResponseJson::successfulResponse(Fractal::collection(
                $this->repository->list($filters),
                ListTransformer::class,
                'employment_profiles'
            ));
        }

        abort(404);
    }
}
