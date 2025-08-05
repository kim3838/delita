<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\SalaryStatementModule\ReOrderSalaryStatementModuleRequest;
use App\Transformers\SalaryStatementModule\ListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SalaryStatementModuleController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(SalaryStatementModuleRepository::class)->list($filters),
                ListTransformer::class,
                'salary_statement_modules'
            ));
        }

        abort(404);
    }

    public function reOrder(ReOrderSalaryStatementModuleRequest $request)
    {
        if($request->expectsJson()){

            $orderables = json_decode($request->get('orderables'));

            App::make(SalaryStatementModuleRepository::class)->reOrder($orderables);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
