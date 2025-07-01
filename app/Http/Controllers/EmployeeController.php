<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\Employee\BasicListTransformer;
use App\Transformers\Employee\ItemTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){
            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(EmployeeRepository::class)->list(),
                BasicListTransformer::class,
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
}
