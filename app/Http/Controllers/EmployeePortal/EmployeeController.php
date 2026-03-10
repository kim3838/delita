<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePortal\Employee\ViewEmployeeRequest;
use App\Transformers\Employee\ItemTransformer;
use App\Transformers\Employee\ListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            $relations = ['user', 'department', 'designation', 'current_employment_profile', 'current_shift', 'upcoming_shift'];

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(EmployeeRepository::class)->paginate($filters, $relations),
                ListTransformer::class,
            ));
        }

        abort(404);
    }

    public function show(Request $request, $ulid)
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
