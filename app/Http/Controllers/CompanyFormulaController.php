<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\CompanyFormula\ItemTransformer as CompanyFormulaTransformer;
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

    public function show($companyFormulaId)
    {
        if(request()->expectsJson()){
            return ResponseJson::successfulResponse([
                'company_formula' => Fractal::item(
                    App::make(CompanyFormulaRepository::class)->show($companyFormulaId),
                    CompanyFormulaTransformer::class
                ),
            ]);
        }

        abort(404);
    }
}
