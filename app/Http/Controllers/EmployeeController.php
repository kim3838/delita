<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\EmployeeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\Employee\ItemTransformer;
use App\Transformers\Employee\ListTransformer;
use App\Transformers\Employee\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(EmployeeRepository::class)->list(),
                ListTransformer::class,
            ));
        }

        abort(404);
    }

    public function selection()
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse([
                'selection' => Fractal::collection(
                    App::make(EmployeeRepository::class)->selection(),
                    SelectionTransformer::class
                )
            ]);
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

    public function check(Request $request, $ulid)
    {
        if($request->expectsJson()){

            $employee = App::make(EmployeeRepository::class)->check($ulid);

            return ResponseJson::successfulResponse(['employee' => $employee]);
        }

        abort(404);
    }
}
