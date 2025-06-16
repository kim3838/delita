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

            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(SalaryStatementModuleRepository::class)->list(),
                ListTransformer::class,
                'salary_statement_modules'
            ));
        }

        abort(404);
    }

    public function reOrder(ReOrderSalaryStatementModuleRequest $request)
    {
        if($request->expectsJson()){

            App::make(SalaryStatementModuleRepository::class)->reOrder();

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
