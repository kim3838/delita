<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\CompanyFormula\SelectionTransformer;
use Illuminate\Support\Facades\App;

class CompanyFormulaController extends Controller
{
    public function selection()
    {
        if(request()->expectsJson()){

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(CompanyFormulaRepository::class)->selection(),
                    SelectionTransformer::class,
                    'selection'
                )
            );
        }

        abort(404);
    }
}
