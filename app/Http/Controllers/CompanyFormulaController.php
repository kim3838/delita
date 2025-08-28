<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Transformers\CompanyFormula\ListTransformer;
use App\Transformers\CompanyFormula\ItemTransformer;
use App\Transformers\CompanyFormula\SelectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CompanyFormulaController extends Controller
{
    public function index(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(CompanyFormulaRepository::class)->list($filters),
                    ListTransformer::class,
                    'formula_settings'
                )
            );
        }

        abort(404);
    }

    public function selection(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection(
                    App::make(CompanyFormulaRepository::class)->selection($filters),
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
                    ItemTransformer::class
                ),
            ]);
        }

        abort(404);
    }

    public function sync(Request $request, $companyId)
    {
        if($request->expectsJson()){

            $data = App::make(CompanyFormulaRepository::class)
                ->sync($companyId, $request->all());

            return ResponseJson::successfulResponse($data);
        }

        abort(404);
    }

    public function syncWithoutDetaching(Request $request, $companyId)
    {
        if($request->expectsJson()){

            $data = App::make(CompanyFormulaRepository::class)
                ->syncWithoutDetaching($companyId, $request->get('formula_ulids', []));

            return ResponseJson::successfulResponse($data);
        }

        abort(404);
    }
}
