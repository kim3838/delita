<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\CompanyIncomeTaxRepository;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\IncomeTax\DestroyIncomeTaxRequest;
use App\Http\Requests\IncomeTax\StoreIncomeTaxRequest;
use App\Http\Requests\IncomeTax\UpdateIncomeTaxRequest;
use App\Transformers\CompanyIncomeTax\ListTransformer;
use App\Transformers\IncomeTax\ItemTransformer as IncomeTaxTransformer;
use Illuminate\Support\Facades\App;

class IncomeTaxController extends Controller
{
    public function index()
    {
        if(request()->expectsJson()){
            return ResponseJson::successfulResponse(Fractal::collection(
                App::make(CompanyIncomeTaxRepository::class)->list(),
                ListTransformer::class,
                'income_taxes'
            ));
        }

        abort(404);
    }

    public function store(StoreIncomeTaxRequest $request)
    {
        if($request->expectsJson()){
            return ResponseJson::successfulResponse([
                'income_tax' => Fractal::item(
                    App::make(IncomeTaxRepository::class)->store($request->validated()),
                    IncomeTaxTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function update(UpdateIncomeTaxRequest $request, $incomeTaxId)
    {
        if($request->expectsJson()){
            return ResponseJson::successfulResponse([
                'income_tax' => Fractal::item(
                    App::make(IncomeTaxRepository::class)->update($incomeTaxId, $request->validated()),
                    IncomeTaxTransformer::class
                )
            ]);
        }

        abort(404);
    }

    public function destroy(DestroyIncomeTaxRequest $request, $incomeTaxId)
    {
        if($request->expectsJson()){

            App::make(IncomeTaxRepository::class)->delete($incomeTaxId);

            return ResponseJson::successfulResponse();
        }

        abort(404);
    }
}
